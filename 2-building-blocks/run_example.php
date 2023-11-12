<?php

use App\Application\Order\CancelOrder;
use App\Application\Order\PlaceOrder;
use App\Domain\Order\OrderRepository;
use App\Domain\OrderPromotion\ProductStockRepository;
use App\Domain\OrderPromotion\RegisterProduct;
use App\Infrastructure\DoctrineORMOrderRepository;
use App\Infrastructure\DoctrineORMProductStockRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ecotone\Dbal\DbalConnection;
use Ecotone\Dbal\ManagerRegistryEmulator;
use Ecotone\Lite\EcotoneLiteApplication;
use Ecotone\Messaging\Config\ConfiguredMessagingSystem;
use Ecotone\Messaging\Handler\DestinationResolutionException;
use Ecotone\Messaging\Handler\Logger\EchoLogger;
use Ecotone\OpenTelemetry\Support\OTelTracer;
use Enqueue\Dbal\DbalConnectionFactory;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

require __DIR__ . "/vendor/autoload.php";

$ecotoneLite = bootstrapEcotone();

try {
    $orderId = Uuid::uuid4()->toString();
    $productId = Uuid::uuid4()->toString();
    $ecotoneLite->getCommandBus()->send(new RegisterProduct($productId, 10));

    comment("Składam zamówienie...");
    $ecotoneLite->getCommandBus()->send(new PlaceOrder(Uuid::uuid4()->toString(), $productId));
    $ecotoneLite->getCommandBus()->send(new PlaceOrder($orderId, $productId));
    comment("Zamówienie złożone");
    comment('Anuluje zamówienie...');
    try {
        $ecotoneLite->getCommandBus()->send(new CancelOrder($orderId));
    }catch (DestinationResolutionException) {
        $ecotoneLite->getCommandBus()->sendWithRouting('order.cancel', metadata: ['aggregate.id' => $orderId]);
    }
    comment('Zamówienie zostało anulowane');

    comment('Sprawdzam czy stan magazynowy się zgadza...');
    Assert::assertSame(9, $ecotoneLite->getQueryBus()->sendWithRouting("getProductStock", metadata: ['aggregate.id' => $productId]), "Stan magazynowy jest niepoprawny.");

    comment("\nZamówienie zostało złożone i anulowane, stan magazynowy się zgadza. Refactoring został przeprowadzony poprawnie :)\n");
} catch (Exception $exception) {
    echo "\n\033[31mNie powiódł się proces anulowania zamówienia. Refactoring zmienił zachowanie systemu.\033[0m\n";
    echo "\033[31mBłąd:\033[0m " . $exception->getMessage() . "\n";
    echo "\033[31mPlik:\033[0m " . $exception->getFile() . "\n";
}

function comment(string $comment): void
{
    echo sprintf("\033[38;5;220mcomment:\033[0m %s\n", $comment);
}

function bootstrapEcotone(): ConfiguredMessagingSystem
{
    $connection = (new DbalConnectionFactory(getenv('DATABASE_DSN') ? getenv('DATABASE_DSN') : 'pgsql://ecotone:secret@localhost:4002/ecotone'))->createContext()->getDbalConnection();
    $managerRegistry = new ManagerRegistryEmulator($connection, [__DIR__ . '/src/Domain']);

    if (!$connection->createSchemaManager()->tablesExist('orders')) {
        $connection->executeStatement(<<<SQL
            CREATE TABLE orders (
                order_id UUID PRIMARY KEY,
                product_id VARCHAR(255),
                is_cancelled BOOLEAN
            )
        SQL
        );
    }
    if (!$connection->createSchemaManager()->tablesExist('product_stocks')) {
        $connection->executeStatement(<<<SQL
            CREATE TABLE product_stocks (
                product_id VARCHAR(255) PRIMARY KEY,
                product_stock_count INT
            )
        SQL
        );
    }

    $services = [
        DbalConnectionFactory::class => DbalConnection::createForManagerRegistry($managerRegistry, 'default'),
        ManagerRegistry::class => $managerRegistry,
        'logger' => new EchoLogger(),
        TracerProviderInterface::class => OTelTracer::create('http://jaeger:4317'),
        OrderRepository::class => new DoctrineORMOrderRepository($managerRegistry),
        ProductStockRepository::class => new DoctrineORMProductStockRepository($managerRegistry),
    ];

    return EcotoneLiteApplication::bootstrap(
        $services,
        pathToRootCatalog: __DIR__
    );
}
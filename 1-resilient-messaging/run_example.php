<?php

use App\Application\PlaceOrder;
use App\Domain\OrderRepository;
use App\Domain\ShippingService;
use App\Infrastructure\DoctrineORMOrderRepository;
use App\Infrastructure\NetworkFailingShippingService;
use Ecotone\Dbal\DbalConnection;
use Ecotone\Dbal\ManagerRegistryEmulator;
use Ecotone\Dbal\Recoverability\DeadLetterGateway;
use Ecotone\Lite\EcotoneLiteApplication;
use Ecotone\Messaging\Config\ConfiguredMessagingSystem;
use Ecotone\Messaging\Config\ServiceConfiguration;
use Ecotone\Messaging\Endpoint\ExecutionPollingMetadata;
use Ecotone\Messaging\Handler\Logger\EchoLogger;
use Ecotone\OpenTelemetry\Support\OTelTracer;
use Enqueue\AmqpExt\AmqpConnectionFactory;
use Enqueue\Dbal\DbalConnectionFactory;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

require __DIR__ . "/vendor/autoload.php";

$serviceName = 'resilient_service';
$ecotoneLite = bootstrapEcotone($serviceName);
$orderPollingMetadata = ExecutionPollingMetadata::createWithDefaults()->withExecutionTimeLimitInMilliseconds(10000)->withHandledMessageLimit(4);
$distributedPollingMetadata = ExecutionPollingMetadata::createWithDefaults()->withExecutionTimeLimitInMilliseconds(60000)->withHandledMessageLimit(1);
cleanup($ecotoneLite, $serviceName);

try {
    comment("Składam zamówienie...");
    $ecotoneLite->getCommandBus()->send(new PlaceOrder(Uuid::uuid4()->toString(), "Laptop"));

    comment("Ecotone tworzy kolejki w RabbitMQ automatycznie (http://localhost:4001/#/queues - guest:guest) oraz samu zajmuje się serializacje i deserializacją do JSON'a.");
    comment("Oczekuje na asynchroniczną wiadomość...");
    $ecotoneLite->run("orders", $orderPollingMetadata);

    comment("Ponów wiadomość z Ecotone Pulse: http://localhost:4000, aby zakończyć zadanie :)");
    comment('Oczekuje na asynchroniczną wiadomość...');
    $ecotoneLite->run($serviceName, $distributedPollingMetadata);

    comment('Wiadomość ponowiona z Dead Letter do kanału `orders`. Przetwarzamy ją ponownie...');
    $ecotoneLite->run('orders', $orderPollingMetadata);

    Assert::assertTrue($ecotoneLite->getQueryBus()->sendWithRouting("isShippingSuccessful"), "Wiadomość nie została ponowiona z Ecotone'a Pulse poprawnie.");
    comment("Zamówienie zostało zapisane w bazie danych i dostarczone do klienta. Gratulacje, zadanie wykonane! :)\n");
    comment('Możesz wejść na Tracing (Jaeger UI): http://localhost:4004/search aby zobaczyć jak przebiegało flow.');
}catch (\Exception $exception) {
    echo "\n\033[31mStracono zamówienie, po drodze wystąpił błąd. System nie jest odporny na błędy.\033[0m\n";
    comment("Wejdz na Tracing (Jaeger UI): http://localhost:4004/search aby zobaczyć dokładne informacje o błędzie.");
    echo "\033[31mBłąd:\033[0m " . $exception->getMessage() . "\n";
    echo "\033[31mPlik:\033[0m " . $exception->getFile() . "\n";
}

function cleanup(ConfiguredMessagingSystem $ecotoneLite, string $serviceName): void
{
    /** @var AmqpConnectionFactory $amqpConnectionFactory */
    $amqpConnectionFactory = $ecotoneLite->getServiceFromContainer(AmqpConnectionFactory::class);
    $amqpConnectionFactory->createContext()->deleteQueue(new \Interop\Amqp\Impl\AmqpQueue('orders'));
    $amqpConnectionFactory->createContext()->deleteQueue(new \Interop\Amqp\Impl\AmqpQueue('distributed_' . $serviceName));
    $ecotoneLite->getGatewayByName(DeadLetterGateway::class)->deleteAll();
}

function comment(string $comment): void
{
    echo sprintf("\033[38;5;220mcomment:\033[0m %s\n", $comment);
}

function bootstrapEcotone(string $serviceName): ConfiguredMessagingSystem
{
    $shippingService = new NetworkFailingShippingService();
    $connection = (new DbalConnectionFactory(getenv('DATABASE_DSN') ? getenv('DATABASE_DSN') : 'pgsql://ecotone:secret@localhost:4002/ecotone'))->createContext()->getDbalConnection();
    $managerRegistry = new ManagerRegistryEmulator($connection, [__DIR__ . '/src/Domain']);

    if (! $connection->createSchemaManager()->tablesExist('orders')) {
        $connection->executeStatement(<<<SQL
            CREATE TABLE orders (
                order_id UUID PRIMARY KEY,
                product_name VARCHAR(255),
                is_cancelled BOOLEAN NOT NULL DEFAULT FALSE
            )
        SQL);
    }

    return EcotoneLiteApplication::bootstrap(
        [
            ShippingService::class => $shippingService,
            OrderRepository::class => new DoctrineORMOrderRepository($managerRegistry),
            NetworkFailingShippingService::class => $shippingService,
            DbalConnectionFactory::class => DbalConnection::createForManagerRegistry($managerRegistry, 'default'),
            AmqpConnectionFactory::class => new AmqpConnectionFactory(['dsn' => getenv('RABBIT_DSN') ? getenv('RABBIT_DSN') : 'amqp://guest:guest@localhost:4003/%2f']),
            'logger' => new EchoLogger(),
            TracerProviderInterface::class => OTelTracer::create('http://jaeger:4317')
        ],
        serviceConfiguration: ServiceConfiguration::createWithDefaults()
            ->withServiceName($serviceName)
            ->withDefaultErrorChannel('errorChannel'),
        pathToRootCatalog: __DIR__
    );
}
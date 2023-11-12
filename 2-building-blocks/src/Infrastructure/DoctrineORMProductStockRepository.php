<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Order\Order;
use App\Domain\Order\OrderRepository;
use App\Domain\OrderPromotion\ProductStock;
use App\Domain\OrderPromotion\ProductStockRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ecotone\Messaging\Support\Assert;

final class DoctrineORMProductStockRepository implements ProductStockRepository
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function get(string $productId): ProductStock
    {
        $order = $this->managerRegistry->getRepository(ProductStock::class)->find($productId);
        Assert::notNull($order, "Product Stock with id {$productId} not found");

        return $order;
    }

    public function save(ProductStock $productStock): void
    {
        $this->managerRegistry->getManagerForClass($productStock::class)->persist($productStock);
    }
}
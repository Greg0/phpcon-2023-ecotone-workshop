<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Order;
use App\Domain\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Ecotone\Messaging\Support\Assert;

final class DoctrineORMOrderRepository implements OrderRepository
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function get(string $orderId): Order
    {
        $order = $this->managerRegistry->getRepository(Order::class)->find($orderId);
        Assert::notNull($order, "Order with id {$orderId} not found");

        return $order;
    }

    public function save(Order $order): void
    {
        $this->managerRegistry->getManagerForClass($order::class)->persist($order);
    }
}
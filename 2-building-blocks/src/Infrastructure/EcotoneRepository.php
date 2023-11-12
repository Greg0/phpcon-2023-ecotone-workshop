<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Order\Order;
use App\Domain\OrderPromotion\ProductStock;
use Doctrine\Persistence\ManagerRegistry;
use Ecotone\Modelling\Attribute\Repository;
use Ecotone\Modelling\StandardRepository;

#[Repository]
final class EcotoneRepository implements StandardRepository
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function canHandle(string $aggregateClassName): bool
    {
        return Order::class === $aggregateClassName;
    }

    public function findBy(string $aggregateClassName, array $identifiers): ?object
    {
        $repository = $this->managerRegistry->getRepository($aggregateClassName);

        return match ($aggregateClassName) {
            Order::class => $repository->find($identifiers['orderId']),
            ProductStock::class => $repository->find($identifiers['productId']),
            default => throw new \InvalidArgumentException("Unknown aggregate {$aggregateClassName}")
        };
    }

    public function save(array $identifiers, object $aggregate, array $metadata, ?int $versionBeforeHandling): void
    {
        $this->managerRegistry->getManagerForClass($aggregate::class)->persist($aggregate);
    }
}
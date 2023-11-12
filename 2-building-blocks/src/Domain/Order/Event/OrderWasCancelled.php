<?php

declare(strict_types=1);

namespace App\Domain\Order\Event;

final readonly class OrderWasCancelled
{
    public function __construct(
        public string $orderId,
        public string $productId
    ) {}
}
<?php

declare(strict_types=1);

namespace App\Application\Order;

final class CancelOrder
{
    public function __construct(
        public string $orderId
    ) {}
}

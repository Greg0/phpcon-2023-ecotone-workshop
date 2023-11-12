<?php

declare(strict_types=1);

namespace App\Domain\Order;

interface OrderRepository
{
    public function get(string $orderId): Order;

    public function save(Order $order): void;
}
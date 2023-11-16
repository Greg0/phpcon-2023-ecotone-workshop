<?php

declare(strict_types=1);

namespace App\Application\Order;

use App\Domain\Order\Event\OrderWasCancelled;
use App\Domain\Order\Event\OrderWasPlaced;
use App\Domain\Order\Order;
use App\Domain\Order\OrderRepository;
use Ecotone\Modelling\Attribute\CommandHandler;
use Ecotone\Modelling\EventBus;

final class OrderService
{
    #[CommandHandler]
    public function placeOrder(
        PlaceOrder $placeOrder,
        OrderRepository $orderRepository,
        EventBus $eventBus
    ): void
    {
        $order = Order::create($placeOrder->orderId, $placeOrder->productId);
        $orderRepository->save($order);

        $eventBus->publish(new OrderWasPlaced($placeOrder->orderId, $placeOrder->productId));
    }
}

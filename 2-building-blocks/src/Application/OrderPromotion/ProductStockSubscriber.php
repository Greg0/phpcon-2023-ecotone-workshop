<?php

declare(strict_types=1);

namespace App\Application\OrderPromotion;

use App\Domain\Order\Event\OrderWasCancelled;
use App\Domain\Order\Event\OrderWasPlaced;
use App\Domain\OrderPromotion\ProductStockRepository;
use Ecotone\Modelling\Attribute\EventHandler;

final class ProductStockSubscriber
{
    #[EventHandler]
    public function decreaseStock(
        OrderWasPlaced $event, ProductStockRepository $productStockRepository
    ): void
    {
        $productStock = $productStockRepository->get($event->productId);
        $productStock->decreaseStock();
        $productStockRepository->save($productStock);
    }

    #[EventHandler]
    public function increaseStock(
        OrderWasCancelled $event, ProductStockRepository $productStockRepository
    ) {
        $productStock = $productStockRepository->get($event->productId);
        $productStock->increaseStock();
        $productStockRepository->save($productStock);
    }
}
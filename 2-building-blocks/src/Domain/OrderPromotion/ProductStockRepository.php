<?php

declare(strict_types=1);

namespace App\Domain\OrderPromotion;

interface ProductStockRepository
{
    public function save(ProductStock $productStock): void;

    public function get(string $productId): ProductStock;
}
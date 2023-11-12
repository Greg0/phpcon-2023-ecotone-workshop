<?php

declare(strict_types=1);

namespace App\Domain\OrderPromotion;

final class RegisterProduct
{
    public function __construct(
        public string $productId,
        public int $quantity
    ) {}
}
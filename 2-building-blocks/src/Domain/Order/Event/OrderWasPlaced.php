<?php

declare(strict_types=1);

namespace App\Domain\Order\Event;

use Ecotone\Modelling\Attribute\NamedEvent;

#[NamedEvent(name: 'order.placed')]
final readonly class OrderWasPlaced
{
    public function __construct(
        public string $orderId,
        public string $productId
    ) {}
}

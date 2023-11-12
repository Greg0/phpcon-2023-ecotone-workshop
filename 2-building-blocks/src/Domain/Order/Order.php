<?php

declare(strict_types=1);

namespace App\Domain\Order;

use Doctrine\ORM\Mapping as ORM;
use Ecotone\Modelling\Attribute\Aggregate;
use Ecotone\Modelling\Attribute\Identifier;
use Ecotone\Modelling\WithEvents;

#[Aggregate]
#[ORM\Entity]
#[ORM\Table(name: 'orders')]
final class Order
{
    use WithEvents;

    #[Identifier]
    #[ORM\Id]
    #[ORM\Column(name: 'order_id', type: 'string')]
    private string $orderId;
    #[ORM\Column(name: 'product_id', type: 'string')]
    private string $productId;
    #[ORM\Column(name: 'is_cancelled', type: 'boolean')]
    private bool $isCancelled;

    public function __construct(
        string $orderId,
        string $productId
    ) {
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->isCancelled = false;
    }

    public static function create(string $orderId, string $productId): self
    {
        return new self($orderId, $productId);
    }

    public function cancel(): void
    {
        $this->isCancelled = true;
    }

    public function productId(): string
    {
        return $this->productId;
    }
}
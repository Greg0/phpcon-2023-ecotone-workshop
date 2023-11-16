<?php

declare(strict_types=1);

namespace App\Domain\Order;

use App\Application\Order\PlaceOrder;
use App\Domain\Order\Event\OrderWasCancelled;
use App\Domain\Order\Event\OrderWasPlaced;
use Doctrine\ORM\Mapping as ORM;
use Ecotone\Modelling\Attribute\Aggregate;
use Ecotone\Modelling\Attribute\CommandHandler;
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
        $this->recordThat(new OrderWasPlaced($this->orderId, $this->productId));
    }

    #[CommandHandler]
    public static function create(PlaceOrder $cmd): self
    {
        return new self($cmd->orderId, $cmd->productId);
    }

    #[CommandHandler(routingKey: 'order.cancel')]
    public function cancel(): void
    {
        $this->isCancelled = true;
        $this->recordThat(new OrderWasCancelled($this->orderId, $this->productId));
    }

    public function productId(): string
    {
        return $this->productId;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\OrderPromotion;

use Doctrine\ORM\Mapping as ORM;
use Ecotone\Modelling\Attribute\Aggregate;
use Ecotone\Modelling\Attribute\CommandHandler;
use Ecotone\Modelling\Attribute\EventHandler;
use Ecotone\Modelling\Attribute\Identifier;
use Ecotone\Modelling\Attribute\QueryHandler;

#[Aggregate]
#[ORM\Entity]
#[ORM\Table(name: 'product_stocks')]
final class ProductStock
{
    #[Identifier]
    #[ORM\Id]
    #[ORM\Column(name: 'product_id', type: 'string')]
    private string $productId;
    #[ORM\Column(name: 'product_stock_count', type: 'integer')]
    private int $productStockCount;

    public function __construct(
        string $productId,
        int $productStockCount
    ) {
        $this->productId = $productId;
        $this->productStockCount = $productStockCount;
    }

    #[CommandHandler]
    public static function create(RegisterProduct $command): self
    {
        return new self($command->productId, $command->quantity);
    }

    #[EventHandler(listenTo: 'order.cancelled')]
    public function increaseStock(): void
    {
        $this->productStockCount++;
    }

    #[EventHandler(listenTo: 'order.placed')]
    public function decreaseStock(): void
    {
        $this->productStockCount--;
    }

    #[QueryHandler("getProductStock")]
    public function getCurrentProductStock(): int
    {
        return $this->productStockCount;
    }
}

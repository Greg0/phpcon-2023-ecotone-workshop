<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Order;
use App\Domain\ShippingService;
use Ecotone\Modelling\Attribute\QueryHandler;

final class NetworkFailingShippingService implements ShippingService
{
    private int $counter = 0;
    private bool $isSuccessful = false;

    /**
     * Ta klasa imituje błąd sieci, który może wystąpić podczas wysyłania zamówienia.
     * Błąd wystąpi 4 razy, a za 5 razem wykona się poprawnie.
     */
    public function ship(Order $order): void
    {
        $this->counter++;

        if ($this->counter <= 4) {
            throw new \RuntimeException("Błąd sieci, podczas komunikacji z Shipping Service.");
        }

        $this->isSuccessful = true;
    }

    #[QueryHandler("isShippingSuccessful")]
    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }
}
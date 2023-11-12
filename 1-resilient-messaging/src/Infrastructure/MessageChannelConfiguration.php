<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Ecotone\Dbal\DbalBackedMessageChannelBuilder;
use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\Messaging\Channel\ExceptionalQueueChannel;
use Ecotone\Messaging\Channel\PollableChannel\GlobalPollableChannelConfiguration;

final class MessageChannelConfiguration
{
    #[ServiceContext]
    public function ordersMessageChannel()
    {
        /**
         *  @TODO Dodaj konfiguracje da asynchronicznego kanalu
         */

        return [];
    }
}
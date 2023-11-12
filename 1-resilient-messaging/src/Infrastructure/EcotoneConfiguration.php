<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Ecotone\Amqp\Configuration\AmqpMessageConsumerConfiguration;
use Ecotone\Amqp\Distribution\AmqpDistributedBusConfiguration;
use Ecotone\Dbal\Configuration\DbalConfiguration;
use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\Messaging\Handler\Recoverability\ErrorHandlerConfiguration;
use Ecotone\Messaging\Handler\Recoverability\RetryTemplateBuilder;

/**
 * Konfiguracja na potrzeby warsztatu.
 * W ramach zadania nie musimy tutaj nic zmieniać.
 */
final class EcotoneConfiguration
{
    #[ServiceContext]
    public function retryConfiguration(): ErrorHandlerConfiguration
    {
        /**
         * Ta konfiguracja odpowiada za ponowne przetwarzanie wiadomości, które nie zostały przetworzone poprawnie.
         * Ustawiliśmy maksymalną liczbę prób na 3, po czym wiadomość zostanie przeniesiona do dbal dead letter.
         */
        return ErrorHandlerConfiguration::createWithDeadLetterChannel(
            'errorChannel',
            RetryTemplateBuilder::exponentialBackoff(2000, 2)
                ->maxRetryAttempts(3),
            'dbal_dead_letter'
        );
    }

    #[ServiceContext]
    public function distributedConsumer(): AmqpDistributedBusConfiguration
    {
        /**
         * Distributed Consumer. Potrzebny do przyjmowania zadań z Ecotone Pulse.
         */
        return AmqpDistributedBusConfiguration::createConsumer();
    }
}
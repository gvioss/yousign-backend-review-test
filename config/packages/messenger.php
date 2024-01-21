<?php

declare(strict_types=1);

use App\Bus\AsyncCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\FrameworkConfig;

return static function (ContainerConfigurator $container, FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $messenger->bus('command.bus');

    $messenger
        ->defaultBus('command.bus')
        ->failureTransport('failed');

    /**
     * Transports
     */
    $messenger
        ->transport('failed')
        ->dsn('%env(MESSENGER_TRANSPORT_DSN)%')
        ->options(['queue_name' => 'failed']);
    $messenger
        ->transport('async')
        ->dsn('%env(MESSENGER_TRANSPORT_DSN)%')
        ->options(['queue_name' => 'async'])
        ->retryStrategy()
            ->maxRetries(3)
            ->delay(1000)
            ->multiplier(10);
    $messenger
        ->transport('sync')
        ->dsn('sync://')
        ->options([]);

    /**
     * Routing
     */
    if ('test' === $container->env()) {
        // When testing the app, send all message synchronously
        $messenger->routing('*')->senders(['sync']);
    } else {
        $messenger->routing(AsyncCommand::class)->senders(['async']);
    }
};

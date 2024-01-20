<?php

namespace App\GitHubStatistics\Infrastructure\Bus;

use App\GitHubStatistics\Domain\Bus\Command;
use App\GitHubStatistics\Domain\Bus\CommandBus;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;

class InMemoryCommandBus implements CommandBus
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly LoggerInterface $commandLogger
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function dispatch(Command $command): void
    {
        try {
            $this->commandBus->dispatch($command);
        } catch (NoHandlerForMessageException) {
            throw new \InvalidArgumentException(sprintf('The command has not a valid handler: %s', $command::class));
        } catch (HandlerFailedException $e) {
            $this->commandLogger->critical($e->getMessage(), [
                'command' => get_class($command)
            ]);
            throw $e->getPrevious();
        }
    }
}

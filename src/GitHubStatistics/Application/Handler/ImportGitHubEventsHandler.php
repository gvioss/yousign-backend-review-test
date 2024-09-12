<?php

namespace App\GitHubStatistics\Application\Handler;

use App\GitHubStatistics\Application\CreateEvent;
use App\GitHubStatistics\Application\Dto\Event;
use App\GitHubStatistics\Application\Fetcher\GitHubArchiveFetcher;
use App\GitHubStatistics\Application\ImportGitHubArchive;
use App\GitHubStatistics\Application\Reader\GitHubArchiveReader;
use App\GitHubStatistics\Domain\Bus\CommandBus;
use App\GitHubStatistics\Domain\Bus\CommandHandler;
use App\GitHubStatistics\Domain\Enum\EventType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
class ImportGitHubEventsHandler implements CommandHandler
{
    public function __construct(
        private readonly GitHubArchiveFetcher $fetcher,
        private readonly GitHubArchiveReader $reader,
        private readonly CommandBus $commandBus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ImportGitHubArchive $command): void
    {
        $filename = sprintf('%s-%s.json.gz', $command->date, $command->hour);

        try {
            $filepath = $this->fetcher->fetch($filename);
        } catch (\Exception $e) {
            $this->logger->critical('Archive download fail.', ['filename' => $filename, 'error' => $e->getMessage()]);
            return;
        }

        foreach ($this->reader->read($filepath) as $data) {
            if (!isset($data['type'])) {
                continue;
            }
            if ($type = EventType::getEquivalentFromGHArchiveType($data['type'])) {
                $this->commandBus->dispatch(new CreateEvent(Event::fromArray($type, $data)));
            }
        }

        unlink($filepath);
    }
}

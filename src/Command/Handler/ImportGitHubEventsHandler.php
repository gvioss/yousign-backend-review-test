<?php

namespace App\Command\Handler;

use App\Bus\CommandBus;
use App\Bus\CommandHandler;
use App\Command\CreateEvent;
use App\Command\ImportGitHubArchive;
use App\Dto\Event;
use App\Enum\EventType;
use App\Exception\ImportGithubArchiveException;
use App\Reader\GitHubArchiveReader;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler(bus: 'command.bus')]
class ImportGitHubEventsHandler implements CommandHandler
{
    public function __construct(
        private readonly GitHubArchiveReader $reader,
        private readonly HttpClientInterface $httpClient,
        private readonly CommandBus $commandBus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ImportGitHubArchive $command): void
    {
        $filename = sprintf('%s-%s.json.gz', $command->date, $command->hour);
        $filepath = sprintf('%s/%s', sys_get_temp_dir(), $filename);

        if (!file_exists($filepath)) {
            try {
                $this->download($filename, $filepath);
            } catch (\Exception $e) {
                $this->logger->critical('Archive download fail.', ['filename' => $filename, 'error' => $e->getMessage()]);
            }
        }

        foreach ($this->reader->read($filepath) as $data) {
            if ($type = EventType::getEquivalentFromGHArchiveType($data['type'])) {
                $this->commandBus->dispatch(new CreateEvent(Event::fromArray($type, $data)));
            }
        }

        unlink($filepath);
    }

    /**
     * @throws ImportGithubArchiveException
     */
    private function download(string $filename, string $filepath): void
    {
        $response = $this->httpClient->request('GET', sprintf('https://data.gharchive.org/%s', $filename));

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw ImportGithubArchiveException::missingArchive($filename);
        }

        $file = fopen($filepath, 'w');
        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($file, $chunk->getContent());
        }
        fclose($file);
    }
}

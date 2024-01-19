<?php

namespace App\Command\Handler;

use App\Bus\CommandBus;
use App\Bus\CommandHandler;
use App\Command\CreateEvent;
use App\Command\ImportGitHubArchive;
use App\Dto\Event;
use App\Enum\EventType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler(bus: 'command.bus')]
class ImportGitHubEventsHandler implements CommandHandler
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CommandBus $commandBus
    ) {
    }

    public function __invoke(ImportGitHubArchive $command): void
    {
        $filename = sprintf('%s-%s.json.gz', $command->date, $command->hour);
        $filepath = sprintf('%s/%s', sys_get_temp_dir(), $filename);

        if (!file_exists($filepath)) {
            $this->download($filename, $filepath);
        }

        foreach ($this->read($filepath) as $data) {
            if ($type = EventType::getEquivalentFromGHArchiveType($data['type'])) {
                $this->commandBus->dispatch(new CreateEvent(Event::fromArray($type, $data)));
            }
        }

        unlink($filepath);
    }

    private function download(string $filename, string $filepath): void
    {
        $response = $this->httpClient->request('GET', sprintf('https://data.gharchive.org/%s', $filename));

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \Exception('The requested archive is missing.');
        }

        $file = fopen($filepath, 'w');
        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($file, $chunk->getContent());
        }
        fclose($file);
    }

    private function read(string $filepath): iterable
    {
        $file = gzopen($filepath, 'r');

        while (!gzeof($file)) {
            $event = gzgets($file, 4096);

            if (!json_validate($event)) {
                continue;
            }

            try {
                yield json_decode($event, true, flags: JSON_THROW_ON_ERROR);
            } catch (\Exception) {
                // TODO : we could log things here, to be able to understand why json can't be decoded and adjust logic
                continue;
            }
        }

        gzclose($file);
    }
}

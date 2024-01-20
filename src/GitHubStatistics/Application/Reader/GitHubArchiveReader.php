<?php

namespace App\GitHubStatistics\Application\Reader;

use Psr\Log\LoggerInterface;

class GitHubArchiveReader
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return iterable The events extracted from the GH archive as array
     */
    public function read(string $filepath): iterable
    {
        $file = gzopen($filepath, 'r');

        while (!gzeof($file)) {
            $event = gzgets($file, 4096);

            if (!json_validate($event)) {
                continue;
            }

            try {
                yield json_decode($event, true, flags: JSON_THROW_ON_ERROR);
            } catch (\Exception $e) {
                $this->logger->warning('An error occurred while decoding event.', [
                    'filepath' => $filepath,
                    'error' => $e
                ]);
                continue;
            }
        }

        gzclose($file);
    }
}

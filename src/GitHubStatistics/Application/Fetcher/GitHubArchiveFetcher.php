<?php

namespace App\GitHubStatistics\Application\Fetcher;

use App\GitHubStatistics\Domain\Exception\ImportGithubArchiveException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubArchiveFetcher
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {
    }

    /**
     * @return string Temporary archive path
     *
     * @throws ImportGithubArchiveException
     */
    public function fetch(string $filename): string
    {
        $filepath = sprintf('%s/%s', sys_get_temp_dir(), $filename);

        if (file_exists($filepath)) {
            return $filepath;
        }

        $response = $this->client->request('GET', sprintf('https://data.gharchive.org/%s', $filename));

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw ImportGithubArchiveException::missingArchive($filename);
        }

        $file = fopen($filepath, 'w');
        foreach ($this->client->stream($response) as $chunk) {
            fwrite($file, $chunk->getContent());
        }
        fclose($file);

        return $filepath;
    }
}

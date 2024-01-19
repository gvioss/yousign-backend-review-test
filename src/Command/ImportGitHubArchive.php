<?php

namespace App\Command;

use App\Bus\AsyncCommand;

readonly class ImportGitHubArchive implements AsyncCommand
{
    /**
     * @param string $date A date formatted as YYYY-MM-DD.
     * @param int $hour An hour between 0 and 23.
     */
    public function __construct(
        public string $date,
        public int $hour
    ) {
        if (!\DateTimeImmutable::createFromFormat('!Y-m-d', $this->date)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid date.', $this->date));
        }

        if (!in_array($this->hour, range(0, 23), true)) {
            throw new \InvalidArgumentException('Hour value must be between 0 and 23.');
        }
    }
}

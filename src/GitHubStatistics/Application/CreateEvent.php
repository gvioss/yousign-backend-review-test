<?php

namespace App\GitHubStatistics\Application;

use App\GitHubStatistics\Application\Dto\Event;
use App\GitHubStatistics\Domain\Bus\AsyncCommand;

readonly class CreateEvent implements AsyncCommand
{
    public function __construct(
        public Event $event
    ) {
    }
}

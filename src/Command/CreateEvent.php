<?php

namespace App\Command;

use App\Bus\AsyncCommand;
use App\Dto\Event;

readonly class CreateEvent implements AsyncCommand
{
    public function __construct(
        public Event $event
    ) {
    }
}

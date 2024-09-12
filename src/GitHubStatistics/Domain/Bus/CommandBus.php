<?php

namespace App\GitHubStatistics\Domain\Bus;

interface CommandBus
{
    public function dispatch(Command $command): void;
}

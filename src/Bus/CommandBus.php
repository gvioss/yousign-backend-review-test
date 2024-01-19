<?php

namespace App\Bus;

interface CommandBus
{
    public function dispatch(Command $command): void;
}

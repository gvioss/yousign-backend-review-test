<?php

namespace App\GitHubStatistics\Domain\Repository;

use App\GitHubStatistics\Application\Dto\EventInput;

interface WriteEventRepository
{
    public function update(EventInput $authorInput, int $id): void;
}

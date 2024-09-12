<?php

namespace App\GitHubStatistics\Domain\Repository;

use App\GitHubStatistics\Application\Dto\SearchInput;

interface ReadEventRepository
{
    public function countAll(SearchInput $searchInput): int;
    public function countByType(SearchInput $searchInput): array;
    public function statsByTypePerHour(SearchInput $searchInput): array;
    public function getLatest(SearchInput $searchInput): array;
    public function exist(int $id): bool;
}

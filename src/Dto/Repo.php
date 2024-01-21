<?php

namespace App\Dto;

use App\Entity\Repo as RepoEntity;

readonly class Repo
{
    public function __construct(
        public int $id,
        public string $name,
        public string $url
    ) {
    }

    public static function fromEntity(RepoEntity $repo): self
    {
        return new self(
            $repo->id(),
            $repo->name(),
            $repo->url()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['url']
        );
    }
}

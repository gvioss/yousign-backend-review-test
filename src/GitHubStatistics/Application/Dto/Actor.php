<?php

namespace App\GitHubStatistics\Application\Dto;

use App\GitHubStatistics\Domain\Entity\Actor as ActorEntity;

readonly class Actor
{
    public function __construct(
        public int $id,
        public string $login,
        public string $url,
        public string $avatarUrl
    ) {
    }

    public static function fromEntity(ActorEntity $actor): self
    {
        return new self(
            $actor->id(),
            $actor->login(),
            $actor->url(),
            $actor->avatarUrl()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['login'],
            $data['url'],
            $data['avatar_url']
        );
    }
}

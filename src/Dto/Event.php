<?php

namespace App\Dto;

use App\Entity\Event as EventEntity;
use App\Enum\EventType;

readonly class Event
{
    public function __construct(
        public int $id,
        public EventType $type,
        public Actor $actor,
        public Repo $repo,
        public array $payload,
        public \DateTimeImmutable $createdAt,
        public string|null $comment = null
    ) {
    }

    public static function fromEntity(EventEntity $event): self
    {
        return new Event(
            $event->id(),
            $event->type(),
            Actor::fromEntity($event->actor()),
            Repo::fromEntity($event->repo()),
            $event->payload(),
            $event->createAt(),
            $event->getComment()
        );
    }

    public static function fromArray(EventType $type, array $data): self
    {
        return new Event(
            $data['id'],
            $type,
            Actor::fromArray($data['actor']),
            Repo::fromArray($data['repo']),
            $data['payload'],
            \DateTimeImmutable::createFromFormat(
                \DateTimeImmutable::RFC3339,
                $data['created_at']
            )
        );
    }
}

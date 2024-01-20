<?php

declare(strict_types=1);

namespace App\GitHubStatistics\Domain\Entity;

use App\GitHubStatistics\Domain\Enum\EventType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`event`')]
#[ORM\Index(columns: ['type'], name: 'IDX_EVENT_TYPE')]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private int $id;

    #[ORM\Column(type: Types::STRING, enumType: EventType::class)]
    private EventType $type;

    #[ORM\Column(type: Types::INTEGER)]
    private int $count = 1;

    #[ORM\ManyToOne(targetEntity: Actor::class, cascade: ['persist'])]
    private Actor $actor;

    #[ORM\ManyToOne(targetEntity: Repo::class, cascade: ['persist'])]
    private Repo $repo;

    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true])]
    private array $payload;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createAt;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment;

    public function __construct(
        int $id,
        EventType $type,
        Actor $actor,
        Repo $repo,
        array $payload,
        \DateTimeImmutable $createAt,
        ?string $comment = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->actor = $actor;
        $this->repo = $repo;
        $this->payload = $payload;
        $this->createAt = $createAt;
        $this->comment = $comment;

        if ($type->isCommit()) {
            $this->count = $payload['size'] ?? 1;
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function type(): EventType
    {
        return $this->type;
    }

    public function actor(): Actor
    {
        return $this->actor;
    }

    public function repo(): Repo
    {
        return $this->repo;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function createAt(): \DateTimeImmutable
    {
        return $this->createAt;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}

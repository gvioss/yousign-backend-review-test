<?php

namespace App\Command\Handler;

use App\Bus\CommandHandler;
use App\Command\CreateEvent;
use App\Dto\Actor as ActorDto;
use App\Dto\Repo as RepoDto;
use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
class CreateEventHandler implements CommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(CreateEvent $command): void
    {
        $events = $this->entityManager->getRepository(Event::class);

        if ($events->find($command->event->id)) {
            return;
        }

        $this->entityManager->persist(new Event(
            $command->event->id,
            $command->event->type->value,
            $this->getActorEntity($command->event->actor),
            $this->getRepoEntity($command->event->repo),
            $command->event->payload,
            $command->event->createdAt
        ));

        $this->entityManager->flush();
    }

    private function getActorEntity(ActorDto $dto): Actor
    {
        $actors = $this->entityManager->getRepository(Actor::class);

        if (!$actor = $actors->find($dto->id)) {
            $actor = new Actor(
                $dto->id,
                $dto->login,
                $dto->url,
                $dto->avatarUrl
            );
        }

        return $actor;
    }

    private function getRepoEntity(RepoDto $dto): Repo
    {
        $repos = $this->entityManager->getRepository(Repo::class);

        if (!$repo = $repos->find($dto->id)) {
            $repo = new Repo(
                $dto->id,
                $dto->name,
                $dto->url
            );
        }

        return $repo;
    }
}

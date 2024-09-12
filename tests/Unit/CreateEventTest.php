<?php

namespace App\Tests\Unit;


use App\GitHubStatistics\Application\CreateEvent;
use App\GitHubStatistics\Application\Dto\Actor;
use App\GitHubStatistics\Application\Dto\Event;
use App\GitHubStatistics\Application\Dto\Repo;
use App\GitHubStatistics\Application\Handler\CreateEventHandler;
use App\GitHubStatistics\Domain\Enum\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateEventTest extends KernelTestCase
{
    private CreateEventHandler $handler;
    private EntityManagerInterface $em;

    public function setUp(): void
    {
        self::bootKernel();

        $this->handler = self::getContainer()->get(CreateEventHandler::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * @test
     */
    public function itPersistEventInDatabase(): void
    {
        $event = new Event(
            123456789,
            EventType::PULL_REQUEST,
            new Actor(12345, 'actor_test', 'url', 'avatar_url'),
            new Repo(12345, 'repo_name', 'repo_url'),
            ['payload' => 'test'],
            new \DateTimeImmutable(),
        );

        $this->handler->__invoke(new CreateEvent($event));

        $this->assertCount(1, $this->em->getRepository(\App\GitHubStatistics\Domain\Entity\Actor::class)->findAll());
        $this->assertCount(1, $this->em->getRepository(\App\GitHubStatistics\Domain\Entity\Repo::class)->findAll());
        $this->assertCount(1, $this->em->getRepository(\App\GitHubStatistics\Domain\Entity\Event::class)->findAll());

        /** @var \App\GitHubStatistics\Domain\Entity\Event $entity */
        $entity = $this->em->getRepository(\App\GitHubStatistics\Domain\Entity\Event::class)->findAll()[0];

        $this->assertEquals(123456789, $entity->id());
        $this->assertContains('test', $entity->payload());
    }
}

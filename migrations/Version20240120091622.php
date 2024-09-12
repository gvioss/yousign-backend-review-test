<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240120091622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Use real enum to handle Event type';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ALTER type TYPE VARCHAR(255)');
        $this->addSql('COMMENT ON COLUMN event.type IS NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "event" ALTER type TYPE VARCHAR(255) CHECK(type IN (\'COM\', \'MSG\', \'PR\'))');
        $this->addSql('COMMENT ON COLUMN "event".type IS \'(DC2Type:EventType)\'');
    }
}

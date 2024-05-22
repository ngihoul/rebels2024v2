<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240522130557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Deleting unsuccessful attempts from DB as we use rate limiter from Symfony bundle';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP unsuccessfull_attempts');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` ADD unsuccessfull_attempts SMALLINT DEFAULT 0 NOT NULL');
    }
}

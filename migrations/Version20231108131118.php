<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231108131118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete current_state field from License table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE license DROP current_state, DROP current_place');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE license ADD current_state VARCHAR(255) NOT NULL, ADD current_place VARCHAR(255) NOT NULL');
    }
}

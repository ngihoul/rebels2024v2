<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231204150448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Team table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, coach_id INT NOT NULL, assistant_id INT DEFAULT NULL, players_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_C4E0A61F3C105691 (coach_id), INDEX IDX_C4E0A61FE05387EF (assistant_id), INDEX IDX_C4E0A61FF1849495 (players_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F3C105691 FOREIGN KEY (coach_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FE05387EF FOREIGN KEY (assistant_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FF1849495 FOREIGN KEY (players_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F3C105691');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FE05387EF');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FF1849495');
        $this->addSql('DROP TABLE team');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231204150749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Roster table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE roster (team_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_60B9ADF9296CD8AE (team_id), INDEX IDX_60B9ADF9A76ED395 (user_id), PRIMARY KEY(team_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE roster ADD CONSTRAINT FK_60B9ADF9296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE roster ADD CONSTRAINT FK_60B9ADF9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FF1849495');
        $this->addSql('DROP INDEX IDX_C4E0A61FF1849495 ON team');
        $this->addSql('ALTER TABLE team DROP players_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE roster DROP FOREIGN KEY FK_60B9ADF9296CD8AE');
        $this->addSql('ALTER TABLE roster DROP FOREIGN KEY FK_60B9ADF9A76ED395');
        $this->addSql('DROP TABLE roster');
        $this->addSql('ALTER TABLE team ADD players_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FF1849495 FOREIGN KEY (players_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_C4E0A61FF1849495 ON team (players_id)');
    }
}

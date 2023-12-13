<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231212125441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, place_id INT NOT NULL, team_id INT DEFAULT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', time_meeting TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', time_from TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', time_to TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', opponent VARCHAR(255) DEFAULT NULL, INDEX IDX_3BAE0AA7DA6A219 (place_id), INDEX IDX_3BAE0AA7296CD8AE (team_id), INDEX IDX_3BAE0AA712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE place (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address_street VARCHAR(255) NOT NULL, address_number VARCHAR(255) NOT NULL, address_zipcode VARCHAR(6) NOT NULL, address_locality VARCHAR(255) NOT NULL, address_country VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7DA6A219 FOREIGN KEY (place_id) REFERENCES place (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA712469DE2 FOREIGN KEY (category_id) REFERENCES event_category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7DA6A219');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7296CD8AE');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA712469DE2');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_category');
        $this->addSql('DROP TABLE place');
    }
}

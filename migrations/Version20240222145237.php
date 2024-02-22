<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240222145237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updating Place Entity : street_number nullable & country link to Country Entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE place ADD address_country_id INT NOT NULL, DROP address_country, CHANGE address_number address_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE place ADD CONSTRAINT FK_741D53CD81B2B6EE FOREIGN KEY (address_country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_741D53CD81B2B6EE ON place (address_country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE place DROP FOREIGN KEY FK_741D53CD81B2B6EE');
        $this->addSql('DROP INDEX IDX_741D53CD81B2B6EE ON place');
        $this->addSql('ALTER TABLE place ADD address_country VARCHAR(255) NOT NULL, DROP address_country_id, CHANGE address_number address_number VARCHAR(255) NOT NULL');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107122241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create License table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE license (id INT AUTO_INCREMENT NOT NULL, season VARCHAR(4) NOT NULL, demand_file VARCHAR(255) DEFAULT NULL, uploaded_file VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, status SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE license');
    }
}

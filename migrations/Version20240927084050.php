<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927084050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creating Relation & RelationType for parent/child management';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE relation (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, child_id INT NOT NULL, relation_type_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_62894749727ACA70 (parent_id), INDEX IDX_62894749DD62C21B (child_id), INDEX IDX_62894749DC379EE2 (relation_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE relation_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE relation ADD CONSTRAINT FK_62894749727ACA70 FOREIGN KEY (parent_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE relation ADD CONSTRAINT FK_62894749DD62C21B FOREIGN KEY (child_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE relation ADD CONSTRAINT FK_62894749DC379EE2 FOREIGN KEY (relation_type_id) REFERENCES relation_type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE relation DROP FOREIGN KEY FK_62894749727ACA70');
        $this->addSql('ALTER TABLE relation DROP FOREIGN KEY FK_62894749DD62C21B');
        $this->addSql('ALTER TABLE relation DROP FOREIGN KEY FK_62894749DC379EE2');
        $this->addSql('DROP TABLE relation');
        $this->addSql('DROP TABLE relation_type');
    }
}

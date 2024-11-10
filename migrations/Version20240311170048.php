<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240311170048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creating Message Entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, sent_by_mail TINYINT(1) NOT NULL, is_archived TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B6BD307FF624B39D (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user CHANGE privacy_policy privacy_policy TINYINT(1) DEFAULT 0 NOT NULL, CHANGE unsuccessfull_attempts unsuccessfull_attempts SMALLINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('DROP TABLE message');
        $this->addSql('ALTER TABLE `user` CHANGE privacy_policy privacy_policy TINYINT(1) NOT NULL, CHANGE unsuccessfull_attempts unsuccessfull_attempts SMALLINT NOT NULL');
    }
}

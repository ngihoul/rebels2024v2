<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240926151104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding canUseApp fields to manage parent/child';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD can_use_app_by_id INT DEFAULT NULL, ADD can_use_app TINYINT(1) DEFAULT NULL, ADD can_use_app_from_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A7C50197 FOREIGN KEY (can_use_app_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649A7C50197 ON user (can_use_app_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649A7C50197');
        $this->addSql('DROP INDEX IDX_8D93D649A7C50197 ON `user`');
        $this->addSql('ALTER TABLE `user` DROP can_use_app_by_id, DROP can_use_app, DROP can_use_app_from_date');
    }
}

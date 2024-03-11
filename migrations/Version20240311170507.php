<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240311170507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creating Message Status Entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message_status (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, receiver_id INT NOT NULL, status TINYINT(1) NOT NULL, INDEX IDX_4C27F813537A1329 (message_id), INDEX IDX_4C27F813CD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message_status ADD CONSTRAINT FK_4C27F813537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE message_status ADD CONSTRAINT FK_4C27F813CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message_status DROP FOREIGN KEY FK_4C27F813537A1329');
        $this->addSql('ALTER TABLE message_status DROP FOREIGN KEY FK_4C27F813CD53EDB6');
        $this->addSql('DROP TABLE message_status');
    }
}

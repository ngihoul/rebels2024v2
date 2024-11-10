<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241101095415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, license_id INT NOT NULL, payment_type SMALLINT DEFAULT NULL, status SMALLINT DEFAULT NULL, user_comment LONGTEXT DEFAULT NULL, refusal_comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6D28840D460F904B (license_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_order (id INT AUTO_INCREMENT NOT NULL, payment_id INT NOT NULL, validated_by_id INT DEFAULT NULL, amount NUMERIC(8, 2) NOT NULL, due_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', value_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', comment LONGTEXT DEFAULT NULL, INDEX IDX_A260A52A4C3A3BB (payment_id), INDEX IDX_A260A52AC69DE5E5 (validated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D460F904B FOREIGN KEY (license_id) REFERENCES license (id)');
        $this->addSql('ALTER TABLE payment_order ADD CONSTRAINT FK_A260A52A4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE payment_order ADD CONSTRAINT FK_A260A52AC69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D460F904B');
        $this->addSql('ALTER TABLE payment_order DROP FOREIGN KEY FK_A260A52A4C3A3BB');
        $this->addSql('ALTER TABLE payment_order DROP FOREIGN KEY FK_A260A52AC69DE5E5');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_order');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107124515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE license_detail (license_id INT NOT NULL, license_sub_category_id INT NOT NULL, INDEX IDX_54E81195460F904B (license_id), INDEX IDX_54E81195B8E2D092 (license_sub_category_id), PRIMARY KEY(license_id, license_sub_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE license_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE license_sub_category (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_237E062412469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE license_detail ADD CONSTRAINT FK_54E81195460F904B FOREIGN KEY (license_id) REFERENCES license (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE license_detail ADD CONSTRAINT FK_54E81195B8E2D092 FOREIGN KEY (license_sub_category_id) REFERENCES license_sub_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE license_sub_category ADD CONSTRAINT FK_237E062412469DE2 FOREIGN KEY (category_id) REFERENCES license_category (id)');
        $this->addSql('ALTER TABLE license ADD user_id INT NOT NULL, ADD user_last_update_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE license ADD CONSTRAINT FK_5768F419A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE license ADD CONSTRAINT FK_5768F419A9DCEAEE FOREIGN KEY (user_last_update_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_5768F419A76ED395 ON license (user_id)');
        $this->addSql('CREATE INDEX IDX_5768F419A9DCEAEE ON license (user_last_update_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE license_detail DROP FOREIGN KEY FK_54E81195460F904B');
        $this->addSql('ALTER TABLE license_detail DROP FOREIGN KEY FK_54E81195B8E2D092');
        $this->addSql('ALTER TABLE license_sub_category DROP FOREIGN KEY FK_237E062412469DE2');
        $this->addSql('DROP TABLE license_detail');
        $this->addSql('DROP TABLE license_category');
        $this->addSql('DROP TABLE license_sub_category');
        $this->addSql('ALTER TABLE license DROP FOREIGN KEY FK_5768F419A76ED395');
        $this->addSql('ALTER TABLE license DROP FOREIGN KEY FK_5768F419A9DCEAEE');
        $this->addSql('DROP INDEX IDX_5768F419A76ED395 ON license');
        $this->addSql('DROP INDEX IDX_5768F419A9DCEAEE ON license');
        $this->addSql('ALTER TABLE license DROP user_id, DROP user_last_update_id, DROP created_at, DROP updated_at');
    }
}

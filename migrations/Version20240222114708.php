<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240222114708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Deleting nationality & country string field to add relation field with Country entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD nationality_id INT NOT NULL, ADD country_id INT NOT NULL, DROP nationality, DROP country');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491C9DA55 FOREIGN KEY (nationality_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6491C9DA55 ON user (nationality_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649F92F3E70 ON user (country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6491C9DA55');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649F92F3E70');
        $this->addSql('DROP INDEX IDX_8D93D6491C9DA55 ON `user`');
        $this->addSql('DROP INDEX IDX_8D93D649F92F3E70 ON `user`');
        $this->addSql('ALTER TABLE `user` ADD nationality VARCHAR(50) NOT NULL, ADD country VARCHAR(50) NOT NULL, DROP nationality_id, DROP country_id');
    }
}

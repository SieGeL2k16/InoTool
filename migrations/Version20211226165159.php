<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211226165159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates AppConfig tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE app_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE app_config (id INT NOT NULL, ref_user_id INT DEFAULT NULL, key_name VARCHAR(100) NOT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_318942FC637A8045 ON app_config (ref_user_id)');
        $this->addSql('CREATE UNIQUE INDEX appcfg_uidx_1 ON app_config (key_name, ref_user_id)');
        $this->addSql('ALTER TABLE app_config ADD CONSTRAINT FK_318942FC637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE app_config_id_seq CASCADE');
        $this->addSql('DROP TABLE app_config');
    }
}

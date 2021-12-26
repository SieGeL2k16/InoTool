<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211226192038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE account_categories_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE account_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE account_import_filter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE account_categories (id INT NOT NULL, ref_user_id INT NOT NULL, name VARCHAR(100) NOT NULL, is_fixed BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C668B4CC637A8045 ON account_categories (ref_user_id)');
        $this->addSql('CREATE UNIQUE INDEX uidx_acccatname ON account_categories (ref_user_id, Name)');
        $this->addSql('CREATE TABLE account_data (id INT NOT NULL, ref_category_id INT DEFAULT NULL, ref_user_id INT NOT NULL, booking_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, description TEXT NOT NULL, amount NUMERIC(12, 2) NOT NULL, accounting_number VARCHAR(34) NOT NULL, currency VARCHAR(10) NOT NULL, bank_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_216074C9E84F656E ON account_data (ref_category_id)');
        $this->addSql('CREATE INDEX IDX_216074C9637A8045 ON account_data (ref_user_id)');
        $this->addSql('CREATE UNIQUE INDEX uidx_accdata1 ON account_data (booking_date, description, amount)');
        $this->addSql('COMMENT ON COLUMN account_data.booking_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE account_import_filter (id INT NOT NULL, ref_category_id INT NOT NULL, name VARCHAR(100) NOT NULL, definition VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9CD3D9B6E84F656E ON account_import_filter (ref_category_id)');
        $this->addSql('COMMENT ON COLUMN account_import_filter.created_on IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE account_categories ADD CONSTRAINT FK_C668B4CC637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_data ADD CONSTRAINT FK_216074C9E84F656E FOREIGN KEY (ref_category_id) REFERENCES account_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_data ADD CONSTRAINT FK_216074C9637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_import_filter ADD CONSTRAINT FK_9CD3D9B6E84F656E FOREIGN KEY (ref_category_id) REFERENCES account_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE account_data DROP CONSTRAINT FK_216074C9E84F656E');
        $this->addSql('ALTER TABLE account_import_filter DROP CONSTRAINT FK_9CD3D9B6E84F656E');
        $this->addSql('DROP SEQUENCE account_categories_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE account_data_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE account_import_filter_id_seq CASCADE');
        $this->addSql('DROP TABLE account_categories');
        $this->addSql('DROP TABLE account_data');
        $this->addSql('DROP TABLE account_import_filter');
    }
}

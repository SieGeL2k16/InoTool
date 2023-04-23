<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230423144815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE fl_service_contract_entries_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fl_service_contracts_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fl_service_contract_entries (id INT NOT NULL, ref_user_id INT NOT NULL, ref_contract_id INT NOT NULL, billing_date DATE NOT NULL, billing_fee NUMERIC(12, 2) NOT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AC976E8F637A8045 ON fl_service_contract_entries (ref_user_id)');
        $this->addSql('CREATE INDEX IDX_AC976E8FDF7F1871 ON fl_service_contract_entries (ref_contract_id)');
        $this->addSql('COMMENT ON COLUMN fl_service_contract_entries.billing_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN fl_service_contract_entries.created_on IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE fl_service_contracts (id INT NOT NULL, ref_user_id INT NOT NULL, ref_customer_id INT NOT NULL, contract_name VARCHAR(200) NOT NULL, contract_start_date DATE NOT NULL, contract_end_date DATE DEFAULT NULL, contract_fee NUMERIC(12, 2) NOT NULL, contract_description TEXT DEFAULT NULL, contract_filename VARCHAR(255) DEFAULT NULL, contract_filesize INT DEFAULT NULL, contract_filetype VARCHAR(100) DEFAULT NULL, contract_data BYTEA DEFAULT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_modified_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D5C8968A637A8045 ON fl_service_contracts (ref_user_id)');
        $this->addSql('CREATE INDEX IDX_D5C8968A699C3B7F ON fl_service_contracts (ref_customer_id)');
        $this->addSql('COMMENT ON COLUMN fl_service_contracts.contract_start_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN fl_service_contracts.created_on IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE fl_service_contract_entries ADD CONSTRAINT FK_AC976E8F637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_service_contract_entries ADD CONSTRAINT FK_AC976E8FDF7F1871 FOREIGN KEY (ref_contract_id) REFERENCES fl_service_contracts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_service_contracts ADD CONSTRAINT FK_D5C8968A637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_service_contracts ADD CONSTRAINT FK_D5C8968A699C3B7F FOREIGN KEY (ref_customer_id) REFERENCES fl_customer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE fl_service_contract_entries_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fl_service_contracts_id_seq CASCADE');
        $this->addSql('ALTER TABLE fl_service_contract_entries DROP CONSTRAINT FK_AC976E8F637A8045');
        $this->addSql('ALTER TABLE fl_service_contract_entries DROP CONSTRAINT FK_AC976E8FDF7F1871');
        $this->addSql('ALTER TABLE fl_service_contracts DROP CONSTRAINT FK_D5C8968A637A8045');
        $this->addSql('ALTER TABLE fl_service_contracts DROP CONSTRAINT FK_D5C8968A699C3B7F');
        $this->addSql('DROP TABLE fl_service_contract_entries');
        $this->addSql('DROP TABLE fl_service_contracts');
    }
}

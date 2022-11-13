<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221113164126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE fl_configuration_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fl_configuration (id INT NOT NULL, ref_user_id INT NOT NULL, company_name VARCHAR(255) NOT NULL, company_owner VARCHAR(255) NOT NULL, company_street VARCHAR(255) DEFAULT NULL, company_city VARCHAR(255) DEFAULT NULL, company_postal VARCHAR(20) DEFAULT NULL, company_country VARCHAR(255) DEFAULT NULL, company_tax_number VARCHAR(30) NOT NULL, company_email VARCHAR(255) DEFAULT NULL, company_phone VARCHAR(40) DEFAULT NULL, company_fax VARCHAR(40) DEFAULT NULL, company_logo BYTEA DEFAULT NULL, bank_name VARCHAR(255) DEFAULT NULL, bank_account VARCHAR(100) DEFAULT NULL, bank_iban VARCHAR(40) DEFAULT NULL, bank_bic VARCHAR(12) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5AA9DF0F637A8045 ON fl_configuration (ref_user_id)');
        $this->addSql('ALTER TABLE fl_configuration ADD CONSTRAINT FK_5AA9DF0F637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE fl_configuration_id_seq CASCADE');
        $this->addSql('ALTER TABLE fl_configuration DROP CONSTRAINT FK_5AA9DF0F637A8045');
        $this->addSql('DROP TABLE fl_configuration');
    }
}

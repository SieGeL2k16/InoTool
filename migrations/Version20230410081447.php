<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410081447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE fl_invoices_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fl_invoices (id INT NOT NULL, ref_user_id INT NOT NULL, ref_customer_id INT NOT NULL, invoice_type INT NOT NULL, is_paid BOOLEAN NOT NULL, invoice_date DATE NOT NULL, invoice_number VARCHAR(30) NOT NULL, sum_netto NUMERIC(10, 2) DEFAULT NULL, sum_brutto NUMERIC(10, 2) DEFAULT NULL, tax_pct NUMERIC(5, 2) DEFAULT NULL, no_tax BOOLEAN NOT NULL, comment TEXT DEFAULT NULL, pdf_filename VARCHAR(255) DEFAULT NULL, pdf_filesize INT DEFAULT NULL, pdf_mime_type VARCHAR(100) DEFAULT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_modified_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, pdf_data BYTEA DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BADB7732637A8045 ON fl_invoices (ref_user_id)');
        $this->addSql('CREATE INDEX IDX_BADB7732699C3B7F ON fl_invoices (ref_customer_id)');
        $this->addSql('COMMENT ON COLUMN fl_invoices.created_on IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE fl_invoices ADD CONSTRAINT FK_BADB7732637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_invoices ADD CONSTRAINT FK_BADB7732699C3B7F FOREIGN KEY (ref_customer_id) REFERENCES fl_customer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_configuration DROP CONSTRAINT FK_5AA9DF0F637A8045');
        $this->addSql('ALTER TABLE fl_configuration ADD CONSTRAINT FK_5AA9DF0F637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE fl_invoices_id_seq CASCADE');
        $this->addSql('ALTER TABLE fl_invoices DROP CONSTRAINT FK_BADB7732637A8045');
        $this->addSql('ALTER TABLE fl_invoices DROP CONSTRAINT FK_BADB7732699C3B7F');
        $this->addSql('DROP TABLE fl_invoices');
        $this->addSql('ALTER TABLE fl_configuration DROP CONSTRAINT fk_5aa9df0f637a8045');
        $this->addSql('ALTER TABLE fl_configuration ADD CONSTRAINT fk_5aa9df0f637a8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

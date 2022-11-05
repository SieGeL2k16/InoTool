<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221105211107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'FlCustomer table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE fl_customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fl_customer (id INT NOT NULL, ref_user_id INT NOT NULL, customer_number VARCHAR(38) NOT NULL, name VARCHAR(200) NOT NULL, contact_email VARCHAR(200) NOT NULL, contact_name VARCHAR(200) DEFAULT NULL, contact_position VARCHAR(100) DEFAULT NULL, active BOOLEAN NOT NULL, street VARCHAR(200) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, country VARCHAR(200) DEFAULT NULL, phone_business VARCHAR(30) DEFAULT NULL, phone_private VARCHAR(30) DEFAULT NULL, phone_mobile VARCHAR(30) DEFAULT NULL, phone_fax VARCHAR(30) DEFAULT NULL, customer_url VARCHAR(255) DEFAULT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_modified_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_51CDD6AE637A8045 ON fl_customer (ref_user_id)');
        $this->addSql('COMMENT ON COLUMN fl_customer.created_on IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE fl_customer ADD CONSTRAINT FK_51CDD6AE637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE fl_customer_id_seq CASCADE');
        $this->addSql('ALTER TABLE fl_customer DROP CONSTRAINT FK_51CDD6AE637A8045');
        $this->addSql('DROP TABLE fl_customer');
    }
}

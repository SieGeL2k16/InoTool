<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220101214624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Handles banking accounts';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE account_bank_accounts_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE account_bank_accounts (id INT NOT NULL, ref_user_id INT NOT NULL, iban VARCHAR(40) NOT NULL, bank_name VARCHAR(80) NOT NULL, bank_shortcut VARCHAR(10) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_64AEB898637A8045 ON account_bank_accounts (ref_user_id)');
        $this->addSql('CREATE UNIQUE INDEX uidx_accbankaccs1 ON account_bank_accounts (ref_user_id, iban)');
        $this->addSql('ALTER TABLE account_bank_accounts ADD CONSTRAINT FK_64AEB898637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE account_bank_accounts_id_seq CASCADE');
        $this->addSql('DROP TABLE account_bank_accounts');
    }
}

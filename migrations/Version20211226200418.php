<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211226200418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixes missing userid field and cosntraints';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uidx_accdata1');
        $this->addSql('CREATE UNIQUE INDEX uidx_accdata1 ON account_data (booking_date, description, amount, ref_user_id)');
        $this->addSql('ALTER TABLE account_import_filter ADD ref_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE account_import_filter ADD CONSTRAINT FK_9CD3D9B6637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9CD3D9B6637A8045 ON account_import_filter (ref_user_id)');
        $this->addSql('CREATE UNIQUE INDEX uidx_accimpfilter1 ON account_import_filter (name, ref_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX uidx_accdata1');
        $this->addSql('CREATE UNIQUE INDEX uidx_accdata1 ON account_data (booking_date, description, amount)');
        $this->addSql('ALTER TABLE account_import_filter DROP CONSTRAINT FK_9CD3D9B6637A8045');
        $this->addSql('DROP INDEX IDX_9CD3D9B6637A8045');
        $this->addSql('DROP INDEX uidx_accimpfilter1');
        $this->addSql('ALTER TABLE account_import_filter DROP ref_user_id');
    }
}

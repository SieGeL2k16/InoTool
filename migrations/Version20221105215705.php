<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221105215705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'FL: Projects + Entries';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE fl_project_entries_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fl_projects_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fl_project_entries (id INT NOT NULL, ref_project_id INT NOT NULL, ref_user_id INT NOT NULL, entry_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, work_time_in_secs INT DEFAULT NULL, status INT NOT NULL, work_description TEXT DEFAULT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_modified_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_42D797DE10A8F082 ON fl_project_entries (ref_project_id)');
        $this->addSql('CREATE INDEX IDX_42D797DE637A8045 ON fl_project_entries (ref_user_id)');
        $this->addSql('COMMENT ON COLUMN fl_project_entries.created_on IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE fl_projects (id INT NOT NULL, ref_customer_id INT NOT NULL, ref_user_id INT NOT NULL, project_name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, status SMALLINT NOT NULL, work_unit INT NOT NULL, pay_per_work_unit INT NOT NULL, currency VARCHAR(10) NOT NULL, time_budget INT NOT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_modified_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, no_reporting BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C67EB03699C3B7F ON fl_projects (ref_customer_id)');
        $this->addSql('CREATE INDEX IDX_8C67EB03637A8045 ON fl_projects (ref_user_id)');
        $this->addSql('COMMENT ON COLUMN fl_projects.created_on IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE fl_project_entries ADD CONSTRAINT FK_42D797DE10A8F082 FOREIGN KEY (ref_project_id) REFERENCES fl_projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_project_entries ADD CONSTRAINT FK_42D797DE637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_projects ADD CONSTRAINT FK_8C67EB03699C3B7F FOREIGN KEY (ref_customer_id) REFERENCES fl_customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_projects ADD CONSTRAINT FK_8C67EB03637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE fl_project_entries_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fl_projects_id_seq CASCADE');
        $this->addSql('ALTER TABLE fl_project_entries DROP CONSTRAINT FK_42D797DE10A8F082');
        $this->addSql('ALTER TABLE fl_project_entries DROP CONSTRAINT FK_42D797DE637A8045');
        $this->addSql('ALTER TABLE fl_projects DROP CONSTRAINT FK_8C67EB03699C3B7F');
        $this->addSql('ALTER TABLE fl_projects DROP CONSTRAINT FK_8C67EB03637A8045');
        $this->addSql('DROP TABLE fl_project_entries');
        $this->addSql('DROP TABLE fl_projects');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221204213820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added missing onDelete=cascade attributes';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fl_customer DROP CONSTRAINT FK_51CDD6AE637A8045');
        $this->addSql('ALTER TABLE fl_customer ADD CONSTRAINT FK_51CDD6AE637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_project_entries DROP CONSTRAINT FK_42D797DE10A8F082');
        $this->addSql('ALTER TABLE fl_project_entries ADD CONSTRAINT FK_42D797DE10A8F082 FOREIGN KEY (ref_project_id) REFERENCES fl_projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_projects DROP CONSTRAINT FK_8C67EB03699C3B7F');
        $this->addSql('ALTER TABLE fl_projects DROP CONSTRAINT FK_8C67EB03637A8045');
        $this->addSql('ALTER TABLE fl_projects ADD CONSTRAINT FK_8C67EB03699C3B7F FOREIGN KEY (ref_customer_id) REFERENCES fl_customer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_projects ADD CONSTRAINT FK_8C67EB03637A8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE fl_customer DROP CONSTRAINT fk_51cdd6ae637a8045');
        $this->addSql('ALTER TABLE fl_customer ADD CONSTRAINT fk_51cdd6ae637a8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_project_entries DROP CONSTRAINT fk_42d797de10a8f082');
        $this->addSql('ALTER TABLE fl_project_entries ADD CONSTRAINT fk_42d797de10a8f082 FOREIGN KEY (ref_project_id) REFERENCES fl_projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_projects DROP CONSTRAINT fk_8c67eb03699c3b7f');
        $this->addSql('ALTER TABLE fl_projects DROP CONSTRAINT fk_8c67eb03637a8045');
        $this->addSql('ALTER TABLE fl_projects ADD CONSTRAINT fk_8c67eb03699c3b7f FOREIGN KEY (ref_customer_id) REFERENCES fl_customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fl_projects ADD CONSTRAINT fk_8c67eb03637a8045 FOREIGN KEY (ref_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

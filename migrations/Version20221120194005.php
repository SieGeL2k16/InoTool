<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221120194005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds additional informations for company logo';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fl_configuration ADD company_logo_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fl_configuration ADD company_logo_size INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fl_configuration ADD company_logo_mime_type VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE fl_configuration DROP company_logo_name');
        $this->addSql('ALTER TABLE fl_configuration DROP company_logo_size');
        $this->addSql('ALTER TABLE fl_configuration DROP company_logo_mime_type');
    }
}

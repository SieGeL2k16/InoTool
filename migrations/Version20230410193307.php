<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410193307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates helper function to calculate project salary';
    }

    public function up(Schema $schema): void
    {
    $this->addSql('
      create or replace function calculateProjectEntry(p_seconds numeric, p_workunit numeric, p_payperworkunit numeric) returns numeric
      as $$
      declare
      	v_wmins NUMERIC;
      begin
          IF p_workunit = 0 THEN
            RETURN(NULL);
          END IF;
          v_wmins := p_seconds / 60;
          RETURN(ROUND((v_wmins / p_workunit) * p_payperworkunit,2));
      	return retval;
      end;
      $$ language plpgsql;');
    }

    public function down(Schema $schema): void
    {
    $this->addSql('drop function if exists calculateProjectEntry');
        // this down() migration is auto-generated, please modify it to your needs
    }
}

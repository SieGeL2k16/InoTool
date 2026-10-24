<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Converts all primary keys from Doctrine's SEQUENCE strategy (separate sequence,
 * no column default) to IDENTITY semantics (column default nextval() + owned sequence).
 *
 * Required since doctrine.yaml sets identity_generation_preferences for PostgreSQL
 * to "identity": Doctrine no longer fetches nextval() itself and omits the id on INSERT.
 */
final class Version20261024220000 extends AbstractMigration
{
    /** @var array<string,string> table (as used in SQL) => sequence name */
    private const TABLES = [
        'account_bank_accounts'       => 'account_bank_accounts_id_seq',
        'account_categories'          => 'account_categories_id_seq',
        'account_data'                => 'account_data_id_seq',
        'account_import_filter'       => 'account_import_filter_id_seq',
        'app_config'                  => 'app_config_id_seq',
        'fl_configuration'            => 'fl_configuration_id_seq',
        'fl_customer'                 => 'fl_customer_id_seq',
        'fl_invoices'                 => 'fl_invoices_id_seq',
        'fl_project_entries'          => 'fl_project_entries_id_seq',
        'fl_projects'                 => 'fl_projects_id_seq',
        'fl_service_contract_entries' => 'fl_service_contract_entries_id_seq',
        'fl_service_contracts'        => 'fl_service_contracts_id_seq',
        '"user"'                      => 'user_id_seq',
    ];

    public function getDescription(): string
    {
        return 'Switch primary keys from SEQUENCE to IDENTITY strategy (DEFAULT nextval + OWNED BY)';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TABLES as $table => $seq) {
            // Make sure the sequence is ahead of all existing ids, but never move it backwards
            $this->addSql(sprintf(
                "SELECT setval('%2\$s', GREATEST((SELECT COALESCE(MAX(id), 0) + 1 FROM %1\$s), (SELECT CASE WHEN is_called THEN last_value + 1 ELSE last_value END FROM %2\$s)), false)",
                $table, $seq
            ));
            $this->addSql(sprintf("ALTER TABLE %s ALTER id SET DEFAULT nextval('%s')", $table, $seq));
            $this->addSql(sprintf('ALTER SEQUENCE %s OWNED BY %s.id', $seq, $table));
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::TABLES as $table => $seq) {
            $this->addSql(sprintf('ALTER SEQUENCE %s OWNED BY NONE', $seq));
            $this->addSql(sprintf('ALTER TABLE %s ALTER id DROP DEFAULT', $table));
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Drops academy/transport flag columns from core tables.
 * Run after DropAcademyTransportModuleTables: php spark migrate
 */
class DropAcademyTransportFlagColumns extends Migration
{
    /** @var list<array{0: string, 1: string}> */
    private array $columns = [
        ['campus', 'a_flag'],
        ['campus', 't_flag'],
        ['students', 'a_flag'],
        ['students', 't_flag'],
        ['students', 'transport_discount'],
        ['fee_type', 'a_flag'],
        ['fee_type', 't_flag'],
        ['fee_type', 'is_transport_fee'],
    ];

    public function up(): void
    {
        foreach ($this->columns as [$table, $column]) {
            if ($this->db->tableExists($table) && $this->db->fieldExists($column, $table)) {
                $this->forge->dropColumn($table, $column);
            }
        }
    }

    public function down(): void
    {
        // Not recreated on rollback.
    }
}

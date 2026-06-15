<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Removes legacy hostel h_flag columns from core tables.
 * Run after DropHostelModuleTables: php spark migrate
 */
class DropHostelFlagColumns extends Migration
{
    /** @var list<array{0: string, 1: string}> [table, column] */
    private array $columns = [
        ['campus', 'h_flag'],
        ['fee_type', 'h_flag'],
        ['students', 'h_flag'],
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
        // Hostel flags removed from the application; columns are not recreated on rollback.
    }
}

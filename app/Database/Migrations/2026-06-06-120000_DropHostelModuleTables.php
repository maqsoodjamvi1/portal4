<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Removes legacy hostel module tables and clears hostel flags.
 * Run: php spark migrate
 */
class DropHostelModuleTables extends Migration
{
    /** @var list<string> Child tables first, then parents */
    private array $hostelTables = [
        'h_checkinout',
        'h_student_beds',
        'h_student_bed',
        'h_room_beds',
        'h_block_rooms',
        'h_fee_amount',
        'h_beds',
        'h_rooms',
        'h_blocks',
    ];

    public function up(): void
    {
        $driver = strtolower((string) $this->db->DBDriver);
        if ($driver === 'mysqli' || $driver === 'mysql') {
            $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        }

        foreach ($this->hostelTables as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        if ($driver === 'mysqli' || $driver === 'mysql') {
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        }

        if ($this->db->tableExists('campus') && $this->db->fieldExists('h_flag', 'campus')) {
            $this->db->table('campus')->update(['h_flag' => 0]);
        }

        if ($this->db->tableExists('fee_type') && $this->db->fieldExists('h_flag', 'fee_type')) {
            $this->db->table('fee_type')->update(['h_flag' => 0]);
        }

        if ($this->db->tableExists('students') && $this->db->fieldExists('h_flag', 'students')) {
            $this->db->table('students')->update(['h_flag' => 0]);
        }
    }

    public function down(): void
    {
        // Hostel schema was removed from the application; not recreated on rollback.
    }
}

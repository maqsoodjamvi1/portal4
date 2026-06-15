<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Removes legacy academy and transport module tables.
 * Run: php spark migrate
 */
class DropAcademyTransportModuleTables extends Migration
{
    /** @var list<string> Child tables first */
    private array $moduleTables = [
        'vehicle_students',
        'vehicles',
        'a_student_subjects',
        'a_group_teacher',
        'a_subject_group',
        'a_class_subjects',
        'a_students',
        'a_groups',
        'a_subject',
        'a_classes',
        'a_academic_session',
    ];

    public function up(): void
    {
        $driver = strtolower((string) $this->db->DBDriver);
        if ($driver === 'mysqli' || $driver === 'mysql') {
            $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        }

        $this->purgeAcademyTransportFeeTypes();

        foreach ($this->moduleTables as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        if ($driver === 'mysqli' || $driver === 'mysql') {
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->clearModuleFlags();
    }

    private function purgeAcademyTransportFeeTypes(): void
    {
        if (! $this->db->tableExists('fee_type')) {
            return;
        }

        $where = [];
        if ($this->db->fieldExists('is_transport_fee', 'fee_type')) {
            $where[] = 'is_transport_fee = 1';
        }
        if ($this->db->fieldExists('a_flag', 'fee_type')) {
            $where[] = 'a_flag = 1';
        }
        if ($this->db->fieldExists('t_flag', 'fee_type')) {
            $where[] = 't_flag = 1';
        }
        if ($where === []) {
            return;
        }

        $rows = $this->db->table('fee_type')
            ->select('fee_type_id')
            ->where('(' . implode(' OR ', $where) . ')', null, false)
            ->get()
            ->getResultArray();
        $ids  = array_map(static fn ($r) => (int) ($r['fee_type_id'] ?? 0), $rows);
        $ids  = array_values(array_filter($ids));

        if ($ids === []) {
            return;
        }

        foreach (['fee_amount', 'fee_plan_months'] as $table) {
            if ($this->db->tableExists($table) && $this->db->fieldExists('fee_type_id', $table)) {
                $this->db->table($table)->whereIn('fee_type_id', $ids)->delete();
            }
        }

        $this->db->table('fee_type')->whereIn('fee_type_id', $ids)->delete();
    }

    private function clearModuleFlags(): void
    {
        if ($this->db->tableExists('campus')) {
            $data = [];
            if ($this->db->fieldExists('a_flag', 'campus')) {
                $data['a_flag'] = 0;
            }
            if ($this->db->fieldExists('t_flag', 'campus')) {
                $data['t_flag'] = 0;
            }
            if ($data !== []) {
                $this->db->table('campus')->update($data);
            }
        }

        if ($this->db->tableExists('students')) {
            $data = [];
            if ($this->db->fieldExists('a_flag', 'students')) {
                $data['a_flag'] = 0;
            }
            if ($this->db->fieldExists('t_flag', 'students')) {
                $data['t_flag'] = 0;
            }
            if ($data !== []) {
                $this->db->table('students')->update($data);
            }
        }
    }

    public function down(): void
    {
        // Academy/transport schema removed from the application; not recreated on rollback.
    }
}

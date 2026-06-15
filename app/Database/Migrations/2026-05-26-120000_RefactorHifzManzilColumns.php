<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Manzil is juz-based only (no line_from/line_to). Adds listener + mistake counts.
 */
class RefactorHifzManzilColumns extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('hifz_daily_recitation')) {
            return;
        }

        foreach (['manzil_line_from', 'manzil_line_to', 'manzil_quality', 'manzil_remarks'] as $col) {
            if ($this->db->fieldExists($col, 'hifz_daily_recitation')) {
                $this->forge->dropColumn('hifz_daily_recitation', $col);
            }
        }

        if (! $this->db->fieldExists('manzil_juz_list', 'hifz_daily_recitation')) {
            $this->forge->addColumn('hifz_daily_recitation', [
                'manzil_juz_list' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 60,
                    'null'       => true,
                    'after'      => 'sabqi_remarks',
                ],
            ]);
        }

        if (! $this->db->fieldExists('manzil_listener_type', 'hifz_daily_recitation')) {
            $this->forge->addColumn('hifz_daily_recitation', [
                'manzil_listener_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => true,
                    'default'    => 'teacher',
                    'after'      => 'manzil_juz_list',
                ],
                'manzil_listener_student_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'manzil_listener_type',
                ],
                'manzil_hard_mistakes' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'default'    => 0,
                    'after'      => 'manzil_listener_student_id',
                ],
                'manzil_soft_mistakes' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'default'    => 0,
                    'after'      => 'manzil_hard_mistakes',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('hifz_daily_recitation')) {
            return;
        }

        foreach (['manzil_soft_mistakes', 'manzil_hard_mistakes', 'manzil_listener_student_id', 'manzil_listener_type'] as $col) {
            if ($this->db->fieldExists($col, 'hifz_daily_recitation')) {
                $this->forge->dropColumn('hifz_daily_recitation', $col);
            }
        }

        if (! $this->db->fieldExists('manzil_line_from', 'hifz_daily_recitation')) {
            $this->forge->addColumn('hifz_daily_recitation', [
                'manzil_line_from' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'sabqi_remarks',
                ],
                'manzil_line_to' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'manzil_line_from',
                ],
                'manzil_quality' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'manzil_line_to',
                ],
                'manzil_remarks' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'manzil_quality',
                ],
            ]);
        }
    }
}

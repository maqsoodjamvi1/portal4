<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Para-only Hifz: four independent daily log tables + student state columns.
 */
class HifzParaOnlyTables extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('hifz_students')) {
            $fields = [];

            if (! $this->db->fieldExists('current_para_no', 'hifz_students')) {
                $fields['current_para_no'] = [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'unsigned'   => true,
                    'default'    => 1,
                    'null'       => false,
                ];
            }

            if (! $this->db->fieldExists('sabqi_active_paras', 'hifz_students')) {
                $fields['sabqi_active_paras'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                ];
            }

            if (! $this->db->fieldExists('manzil_pool_paras', 'hifz_students')) {
                $fields['manzil_pool_paras'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                ];
            }

            if ($fields !== []) {
                $this->forge->addColumn('hifz_students', $fields);
            }
        }

        if (! $this->db->tableExists('hifz_mutalia_logs')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'student_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'session_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'campus_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'hifz_sec_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'teacher_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'entry_date' => ['type' => 'DATE'],
                'para_no' => [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'unsigned'   => true,
                ],
                'lines_count' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'para_completed' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'new_para_started' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'new_para_no' => [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'remarks' => ['type' => 'TEXT', 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['student_id', 'entry_date']);
            $this->forge->addKey(['campus_id', 'entry_date']);
            $this->forge->createTable('hifz_mutalia_logs', true);
        }

        if (! $this->db->tableExists('hifz_sabaq_logs')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'student_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'session_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'campus_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'hifz_sec_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'teacher_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'entry_date' => ['type' => 'DATE'],
                'mutalia_log_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'para_no' => [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'unsigned'   => true,
                ],
                'lines_count' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'quality' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                ],
                'remarks' => ['type' => 'TEXT', 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['student_id', 'entry_date']);
            $this->forge->addKey('mutalia_log_id');
            $this->forge->createTable('hifz_sabaq_logs', true);
        }

        if (! $this->db->tableExists('hifz_sabqi_logs')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'student_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'session_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'campus_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'hifz_sec_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'teacher_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'entry_date' => ['type' => 'DATE'],
                'remarks' => ['type' => 'TEXT', 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['student_id', 'entry_date']);
            $this->forge->createTable('hifz_sabqi_logs', true);
        }

        if (! $this->db->tableExists('hifz_sabqi_log_paras')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'sabqi_log_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'para_no' => [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'unsigned'   => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('sabqi_log_id');
            $this->forge->addUniqueKey(['sabqi_log_id', 'para_no']);
            $this->forge->createTable('hifz_sabqi_log_paras', true);
        }

        if (! $this->db->tableExists('hifz_manzil_logs')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'student_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'session_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'campus_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'hifz_sec_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'teacher_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'entry_date' => ['type' => 'DATE'],
                'para_no' => [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'unsigned'   => true,
                ],
                'listener_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => true,
                    'default'    => 'teacher',
                ],
                'listener_student_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'hard_mistakes' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'soft_mistakes' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['student_id', 'entry_date']);
            $this->forge->addUniqueKey(['student_id', 'entry_date', 'para_no']);
            $this->forge->createTable('hifz_manzil_logs', true);
        }
    }

    public function down()
    {
        foreach ([
            'hifz_manzil_logs',
            'hifz_sabqi_log_paras',
            'hifz_sabqi_logs',
            'hifz_sabaq_logs',
            'hifz_mutalia_logs',
        ] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        if ($this->db->tableExists('hifz_students')) {
            foreach (['manzil_pool_paras', 'sabqi_active_paras', 'current_para_no'] as $col) {
                if ($this->db->fieldExists($col, 'hifz_students')) {
                    $this->forge->dropColumn('hifz_students', $col);
                }
            }
        }
    }
}

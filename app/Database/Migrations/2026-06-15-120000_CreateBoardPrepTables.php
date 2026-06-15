<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBoardPrepTables extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('board_prep_users')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'username' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'password_hash' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'display_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                ],
                'father_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                ],
                'grade_level' => [
                    'type'       => 'ENUM',
                    'constraint' => ['ssc1', 'ssc2', 'hssc1', 'hssc2'],
                ],
                'board_publisher_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'linked_student_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['active', 'suspended'],
                    'default'    => 'active',
                ],
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                    'null'       => true,
                ],
                'email_verified_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('username');
            $this->forge->addKey(['grade_level', 'board_publisher_id']);
            $this->forge->addKey('linked_student_id');
            $this->forge->createTable('board_prep_users', true);
        }

        if (! $this->db->tableExists('board_prep_platform')) {
            $this->forge->addField([
                'id' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                ],
                'system_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'campus_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'session_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'term_session_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'grade_cls_sec_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('board_prep_platform', true);
        }

        if ($this->db->tableExists('students') && ! $this->db->fieldExists('account_type', 'students')) {
            $this->forge->addColumn('students', [
                'account_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'default'    => 'school',
                    'after'      => 'status',
                ],
            ]);
        }

        if ($this->db->tableExists('students') && ! $this->db->fieldExists('board_prep_user_id', 'students')) {
            $this->forge->addColumn('students', [
                'board_prep_user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'account_type',
                ],
            ]);
        }

        if ($this->db->tableExists('quizzes')) {
            if (! $this->db->fieldExists('audience', 'quizzes')) {
                $this->forge->addColumn('quizzes', [
                    'audience' => [
                        'type'       => 'ENUM',
                        'constraint' => ['school', 'board_prep', 'both'],
                        'default'    => 'school',
                    ],
                ]);
            }
            if (! $this->db->fieldExists('prep_grade_level', 'quizzes')) {
                $this->forge->addColumn('quizzes', [
                    'prep_grade_level' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 16,
                        'null'       => true,
                    ],
                ]);
            }
            if (! $this->db->fieldExists('prep_board_publisher_id', 'quizzes')) {
                $this->forge->addColumn('quizzes', [
                    'prep_board_publisher_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                    ],
                ]);
            }
        }

        $this->seedBoardPublishers();
    }

    private function seedBoardPublishers(): void
    {
        if (! $this->db->tableExists('qb_board_publishers')) {
            return;
        }

        $boards = [
            ['name' => 'Federal Board (FBISE)', 'short_code' => 'FBISE', 'sort_order' => 1],
            ['name' => 'Rawalpindi Board (RBISE)', 'short_code' => 'RBISE', 'sort_order' => 2],
            ['name' => 'Lahore Board (LBISE)', 'short_code' => 'LBISE', 'sort_order' => 3],
            ['name' => 'Sindh Board', 'short_code' => 'SINDH', 'sort_order' => 4],
            ['name' => 'KPK Board', 'short_code' => 'KPK', 'sort_order' => 5],
        ];

        $now = date('Y-m-d H:i:s');

        foreach ($boards as $board) {
            $exists = $this->db->table('qb_board_publishers')
                ->where('system_id', 0)
                ->where('short_code', $board['short_code'])
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('qb_board_publishers')->insert([
                'system_id'  => 0,
                'name'       => $board['name'],
                'short_code' => $board['short_code'],
                'sort_order' => $board['sort_order'],
                'status'     => 1,
                'created_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('quizzes')) {
            if ($this->db->fieldExists('prep_board_publisher_id', 'quizzes')) {
                $this->forge->dropColumn('quizzes', 'prep_board_publisher_id');
            }
            if ($this->db->fieldExists('prep_grade_level', 'quizzes')) {
                $this->forge->dropColumn('quizzes', 'prep_grade_level');
            }
            if ($this->db->fieldExists('audience', 'quizzes')) {
                $this->forge->dropColumn('quizzes', 'audience');
            }
        }

        if ($this->db->tableExists('students')) {
            if ($this->db->fieldExists('board_prep_user_id', 'students')) {
                $this->forge->dropColumn('students', 'board_prep_user_id');
            }
            if ($this->db->fieldExists('account_type', 'students')) {
                $this->forge->dropColumn('students', 'account_type');
            }
        }

        $this->forge->dropTable('board_prep_platform', true);
        $this->forge->dropTable('board_prep_users', true);
    }
}

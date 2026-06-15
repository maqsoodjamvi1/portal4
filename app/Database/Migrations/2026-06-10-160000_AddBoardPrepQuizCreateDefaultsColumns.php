<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBoardPrepQuizCreateDefaultsColumns extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('quiz_create_defaults')) {
            return;
        }

        if (! $this->db->fieldExists('form_scope', 'quiz_create_defaults')) {
            $this->forge->addColumn('quiz_create_defaults', [
                'form_scope' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'school',
                    'after'      => 'member_id',
                ],
            ]);
        }

        if (! $this->db->fieldExists('prep_grade_level', 'quiz_create_defaults')) {
            $this->forge->addColumn('quiz_create_defaults', [
                'prep_grade_level' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('prep_board_publisher_id', 'quiz_create_defaults')) {
            $this->forge->addColumn('quiz_create_defaults', [
                'prep_board_publisher_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('qb_class_id', 'quiz_create_defaults')) {
            $this->forge->addColumn('quiz_create_defaults', [
                'qb_class_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('qb_subject_id', 'quiz_create_defaults')) {
            $this->forge->addColumn('quiz_create_defaults', [
                'qb_subject_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('questions_count', 'quiz_create_defaults')) {
            $this->forge->addColumn('quiz_create_defaults', [
                'questions_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
            ]);
        }

        // Replace unique (campus_id, member_id) with (campus_id, member_id, form_scope).
        foreach (['campus_id', 'campus_id_member_id'] as $indexName) {
            if ($this->indexExists('quiz_create_defaults', $indexName)) {
                $this->db->query('ALTER TABLE `quiz_create_defaults` DROP INDEX `' . $indexName . '`');
            }
        }

        try {
            $this->db->query(
                'ALTER TABLE `quiz_create_defaults` ADD UNIQUE KEY `quiz_defaults_campus_member_scope` (`campus_id`, `member_id`, `form_scope`)'
            );
        } catch (\Throwable $e) {
            // Unique key may already exist after partial migration.
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('quiz_create_defaults')) {
            return;
        }

        try {
            $this->db->query('ALTER TABLE `quiz_create_defaults` DROP INDEX `quiz_defaults_campus_member_scope`');
        } catch (\Throwable $e) {
        }

        try {
            $this->db->query(
                'ALTER TABLE `quiz_create_defaults` ADD UNIQUE KEY `campus_id` (`campus_id`, `member_id`)'
            );
        } catch (\Throwable $e) {
        }

        foreach (['questions_count', 'qb_subject_id', 'qb_class_id', 'prep_board_publisher_id', 'prep_grade_level', 'form_scope'] as $col) {
            if ($this->db->fieldExists($col, 'quiz_create_defaults')) {
                $this->forge->dropColumn('quiz_create_defaults', $col);
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $row = $this->db->query(
            'SELECT 1 FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?
             LIMIT 1',
            [$table, $indexName]
        )->getRow();

        return $row !== null;
    }
}

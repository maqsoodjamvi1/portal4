<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuizCreateDefaultsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'campus_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'term_session_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'cls_sec_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'sec_sub_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'instructions' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'start_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'end_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'time_limit_min' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'max_attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'per_question_marks' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'default'    => 1,
            ],
            'negative_mark_per_q' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'default'    => 0,
            ],
            'count_mcq_single' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'count_mcq_multi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'count_tf' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'count_fill' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'count_short' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'count_match' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'shuffle_questions' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'shuffle_options' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'show_solution' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'wifi_only' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'is_published' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'is_urdu' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'is_order_by_qtype' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'is_adaptive' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'topic_keys_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['campus_id', 'member_id'], false, true);
        $this->forge->createTable('quiz_create_defaults', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('quiz_create_defaults', true);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCrosswordTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'campus_id'     => ['type' => 'INT', 'unsigned' => true],
            'title'         => ['type' => 'VARCHAR', 'constraint' => 120],
            'puzzle_type'   => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'math_square'],
            'grade'         => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'settings_json' => ['type' => 'TEXT', 'null' => true],
            'student_name'  => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'created_by'    => ['type' => 'INT', 'unsigned' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('campus_id');
        $this->forge->createTable('crossword_sets', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'set_id'      => ['type' => 'INT', 'unsigned' => true],
            'sort_order'  => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'puzzle_json' => ['type' => 'MEDIUMTEXT'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('set_id');
        $this->forge->createTable('crossword_puzzles', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'set_id'      => ['type' => 'INT', 'unsigned' => true],
            'campus_id'   => ['type' => 'INT', 'unsigned' => true],
            'cls_sec_id'  => ['type' => 'INT', 'unsigned' => true],
            'due_date'    => ['type' => 'DATE', 'null' => true],
            'status'      => ['type' => 'TINYINT', 'default' => 1],
            'assigned_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['cls_sec_id', 'status']);
        $this->forge->createTable('crossword_assignments', true);

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'assignment_id' => ['type' => 'INT', 'unsigned' => true],
            'student_id'    => ['type' => 'INT', 'unsigned' => true],
            'answers_json'  => ['type' => 'MEDIUMTEXT', 'null' => true],
            'score'         => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'correct_count' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'total_count'   => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'submitted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['assignment_id', 'student_id']);
        $this->forge->createTable('crossword_attempts', true);
    }

    public function down()
    {
        $this->forge->dropTable('crossword_attempts', true);
        $this->forge->dropTable('crossword_assignments', true);
        $this->forge->dropTable('crossword_puzzles', true);
        $this->forge->dropTable('crossword_sets', true);
    }
}

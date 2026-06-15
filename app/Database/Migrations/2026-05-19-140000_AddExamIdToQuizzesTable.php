<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExamIdToQuizzesTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('quizzes')) {
            return;
        }

        if ($this->db->fieldExists('exam_id', 'quizzes')) {
            return;
        }

        $this->forge->addColumn('quizzes', [
            'exam_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'term_session_id',
            ],
        ]);

        $this->db->query('ALTER TABLE `quizzes` ADD KEY `idx_quizzes_exam_id` (`exam_id`)');
    }

    public function down()
    {
        if (!$this->db->tableExists('quizzes') || !$this->db->fieldExists('exam_id', 'quizzes')) {
            return;
        }

        $this->forge->dropColumn('quizzes', 'exam_id');
    }
}

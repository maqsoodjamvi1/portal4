<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLinkToExamToQuizCreateDefaults extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('quiz_create_defaults')) {
            return;
        }

        if ($this->db->fieldExists('link_to_exam', 'quiz_create_defaults')) {
            return;
        }

        $this->forge->addColumn('quiz_create_defaults', [
            'link_to_exam' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_adaptive',
            ],
        ]);
    }

    public function down()
    {
        if (!$this->db->tableExists('quiz_create_defaults') || !$this->db->fieldExists('link_to_exam', 'quiz_create_defaults')) {
            return;
        }

        $this->forge->dropColumn('quiz_create_defaults', 'link_to_exam');
    }
}

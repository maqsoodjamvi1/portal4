<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTargetFieldsToQuestionPapers extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('question_papers')) {
            return;
        }

        $fields = [];
        if (! $this->db->fieldExists('term_session_id', 'question_papers')) {
            $fields['term_session_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'session_id',
            ];
        }
        if (! $this->db->fieldExists('cls_sec_id', 'question_papers')) {
            $fields['cls_sec_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'term_session_id',
            ];
        }
        if (! $this->db->fieldExists('sec_sub_id', 'question_papers')) {
            $fields['sec_sub_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'cls_sec_id',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('question_papers', $fields);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('question_papers')) {
            return;
        }

        foreach (['sec_sub_id', 'cls_sec_id', 'term_session_id'] as $col) {
            if ($this->db->fieldExists($col, 'question_papers')) {
                $this->forge->dropColumn('question_papers', $col);
            }
        }
    }
}

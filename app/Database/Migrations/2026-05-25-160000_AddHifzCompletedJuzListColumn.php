<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHifzCompletedJuzListColumn extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('hifz_students')) {
            return;
        }

        if (! $this->db->fieldExists('completed_juz_list', 'hifz_students')) {
            $this->forge->addColumn('hifz_students', [
                'completed_juz_list' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                    'after'      => 'current_juz_memorized_lines',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('completed_juz_list', 'hifz_students')) {
            $this->forge->dropColumn('hifz_students', 'completed_juz_list');
        }
    }
}

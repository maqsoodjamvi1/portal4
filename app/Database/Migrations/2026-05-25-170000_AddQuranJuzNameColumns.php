<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQuranJuzNameColumns extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('quran_juz_boundaries')) {
            return;
        }

        if (! $this->db->fieldExists('juz_name_ar', 'quran_juz_boundaries')) {
            $this->forge->addColumn('quran_juz_boundaries', [
                'juz_name_ar' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 80,
                    'null'       => true,
                    'after'      => 'juz_no',
                ],
            ]);
        }

        if (! $this->db->fieldExists('juz_name_en', 'quran_juz_boundaries')) {
            $this->forge->addColumn('quran_juz_boundaries', [
                'juz_name_en' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                    'after'      => 'juz_name_ar',
                ],
            ]);
        }
    }

    public function down()
    {
        foreach (['juz_name_en', 'juz_name_ar'] as $col) {
            if ($this->db->fieldExists($col, 'quran_juz_boundaries')) {
                $this->forge->dropColumn('quran_juz_boundaries', $col);
            }
        }
    }
}

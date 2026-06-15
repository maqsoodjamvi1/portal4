<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHifzManzilRotationColumns extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('hifz_students')) {
            if (! $this->db->fieldExists('manzil_paras_per_day', 'hifz_students')) {
                $this->forge->addColumn('hifz_students', [
                    'manzil_paras_per_day' => [
                        'type'       => 'TINYINT',
                        'constraint' => 1,
                        'unsigned'   => true,
                        'default'    => 1,
                        'after'      => 'mutalia_lines_per_day',
                    ],
                ]);
            }
            if (! $this->db->fieldExists('manzil_rotation_index', 'hifz_students')) {
                $this->forge->addColumn('hifz_students', [
                    'manzil_rotation_index' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'default'    => 0,
                        'after'      => 'manzil_paras_per_day',
                    ],
                ]);
            }
        }

        if ($this->db->tableExists('hifz_daily_recitation')) {
            if (! $this->db->fieldExists('manzil_juz_list', 'hifz_daily_recitation')) {
                $this->forge->addColumn('hifz_daily_recitation', [
                    'manzil_juz_list' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 60,
                        'null'       => true,
                        'after'      => 'manzil_line_to',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        foreach (['manzil_rotation_index', 'manzil_paras_per_day'] as $col) {
            if ($this->db->fieldExists($col, 'hifz_students')) {
                $this->forge->dropColumn('hifz_students', $col);
            }
        }
        if ($this->db->fieldExists('manzil_juz_list', 'hifz_daily_recitation')) {
            $this->forge->dropColumn('hifz_daily_recitation', 'manzil_juz_list');
        }
    }
}

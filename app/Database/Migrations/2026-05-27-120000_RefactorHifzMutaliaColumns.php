<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Remove legacy Mutalia line/quality columns; range is stored as surah/ayah start and end.
 */
class RefactorHifzMutaliaColumns extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('hifz_daily_recitation')) {
            return;
        }

        foreach ([
            'mutalia_line_from',
            'mutalia_line_to',
            'mutalia_quality',
        ] as $col) {
            if ($this->db->fieldExists($col, 'hifz_daily_recitation')) {
                $this->forge->dropColumn('hifz_daily_recitation', $col);
            }
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('hifz_daily_recitation')) {
            return;
        }

        if (! $this->db->fieldExists('mutalia_line_from', 'hifz_daily_recitation')) {
            $this->forge->addColumn('hifz_daily_recitation', [
                'mutalia_line_from' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'mutalia_lines_requested',
                ],
                'mutalia_line_to' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'mutalia_line_from',
                ],
                'mutalia_surah_id_start' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'mutalia_line_to',
                ],
                'mutalia_ayah_from' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'mutalia_surah_id_start',
                ],
                'mutalia_quality' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'mutalia_ayah_to',
                ],
            ]);
        }
    }
}

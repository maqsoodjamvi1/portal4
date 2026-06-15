<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Optional teacher override for Mutalia start (when auto chain is wrong).
 */
class AddHifzMutaliaOptionalStartColumns extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('hifz_daily_recitation')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('mutalia_surah_id_start', 'hifz_daily_recitation')) {
            $fields['mutalia_surah_id_start'] = [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'mutalia_lines_requested',
            ];
        }

        if (! $this->db->fieldExists('mutalia_ayah_from', 'hifz_daily_recitation')) {
            $fields['mutalia_ayah_from'] = [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'mutalia_surah_id_start',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('hifz_daily_recitation', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('hifz_daily_recitation')) {
            return;
        }

        foreach (['mutalia_ayah_from', 'mutalia_surah_id_start'] as $col) {
            if ($this->db->fieldExists($col, 'hifz_daily_recitation')) {
                $this->forge->dropColumn('hifz_daily_recitation', $col);
            }
        }
    }
}

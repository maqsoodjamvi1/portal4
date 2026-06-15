<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Surah-wise reverse memorization progress on hifz_students.
 * reverse_learned_from_surah = N means surahs N+1 … 114 are fully memorized.
 */
class AddHifzSurahProgressColumns extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('hifz_students')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('reverse_learned_from_surah', 'hifz_students')) {
            $fields['reverse_learned_from_surah'] = [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'memorization_sequence',
            ];
        }

        if (! $this->db->fieldExists('current_sabaq_surah_id', 'hifz_students')) {
            $fields['current_sabaq_surah_id'] = [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'reverse_learned_from_surah',
            ];
        }

        if (! $this->db->fieldExists('current_sabaq_ayah', 'hifz_students')) {
            $fields['current_sabaq_ayah'] = [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'current_sabaq_surah_id',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('hifz_students', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('hifz_students')) {
            return;
        }

        foreach (['current_sabaq_ayah', 'current_sabaq_surah_id', 'reverse_learned_from_surah'] as $col) {
            if ($this->db->fieldExists($col, 'hifz_students')) {
                $this->forge->dropColumn('hifz_students', $col);
            }
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Unified Mutalia+Sabaq lesson row on hifz_mutalia_logs (Sabaq updates prior day's row).
 */
class MergeHifzLessonLogColumns extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('hifz_mutalia_logs')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('sabaq_date', 'hifz_mutalia_logs')) {
            $fields['sabaq_date'] = ['type' => 'DATE', 'null' => true];
        }

        if (! $this->db->fieldExists('sabaq_quality', 'hifz_mutalia_logs')) {
            $fields['sabaq_quality'] = ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true];
        }

        if (! $this->db->fieldExists('sabaq_remarks', 'hifz_mutalia_logs')) {
            $fields['sabaq_remarks'] = ['type' => 'TEXT', 'null' => true];
        }

        if (! $this->db->fieldExists('line_from', 'hifz_mutalia_logs')) {
            $fields['line_from'] = [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('line_to', 'hifz_mutalia_logs')) {
            $fields['line_to'] = [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('hifz_mutalia_logs', $fields);
        }

        // lines_count must hold up to 320 per day
        if ($this->db->fieldExists('lines_count', 'hifz_mutalia_logs')) {
            $this->forge->modifyColumn('hifz_mutalia_logs', [
                'lines_count' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('hifz_mutalia_logs')) {
            return;
        }

        foreach (['sabaq_date', 'sabaq_quality', 'sabaq_remarks', 'line_from', 'line_to'] as $col) {
            if ($this->db->fieldExists($col, 'hifz_mutalia_logs')) {
                $this->forge->dropColumn('hifz_mutalia_logs', $col);
            }
        }
    }
}

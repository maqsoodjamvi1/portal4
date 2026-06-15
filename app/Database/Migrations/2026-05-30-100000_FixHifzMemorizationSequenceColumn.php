<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Para-only plan order: store para_reverse (legacy ENUM only had surah_* values).
 */
class FixHifzMemorizationSequenceColumn extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('hifz_students')) {
            return;
        }

        $row = $this->db->query(
            "SHOW COLUMNS FROM `hifz_students` WHERE Field = 'memorization_sequence'"
        )->getRow();

        if (! $row) {
            return;
        }

        $type = strtolower((string) ($row->Type ?? ''));

        if (str_contains($type, 'enum') && ! str_contains($type, 'para_reverse')) {
            $this->db->query(
                "ALTER TABLE `hifz_students` MODIFY `memorization_sequence` VARCHAR(32) NOT NULL DEFAULT 'para_forward'"
            );
        }

        $this->db->query(
            "UPDATE `hifz_students` SET `memorization_sequence` = 'para_reverse'
             WHERE `memorization_sequence` IN ('surah_reverse_full', 'surah_reverse_ayah_reverse')"
        );
    }

    public function down()
    {
        // Non-destructive: leave VARCHAR in place.
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Hifz Quran Program — Phase 1 tables and campus/class flags.
 */
class CreateHifzProgramTables extends Migration
{
    public function up(): void
    {
        $this->addCampusColumns();
        $this->addClassesColumn();
        $this->createQuranReferenceTables();
        $this->createHifzOperationalTables();
    }

    public function down(): void
    {
        $this->forge->dropTable('hifz_daily_recitation', true);
        $this->forge->dropTable('hifz_teacher_sections', true);
        $this->forge->dropTable('hifz_students', true);
        $this->forge->dropTable('hifz_sections', true);
        $this->forge->dropTable('quran_mushaf_lines', true);
        $this->forge->dropTable('quran_juz_boundaries', true);
        $this->forge->dropTable('quran_surahs', true);
        $this->forge->dropTable('quran_mushaf_layouts', true);

        if ($this->db->fieldExists('is_hifz_class', 'classes')) {
            $this->forge->dropColumn('classes', 'is_hifz_class');
        }
        foreach (['hfz_flag', 'default_sabaq_lines', 'default_mutalia_lines', 'hifz_class_id'] as $col) {
            if ($this->db->fieldExists($col, 'campus')) {
                $this->forge->dropColumn('campus', $col);
            }
        }
    }

    private function addCampusColumns(): void
    {
        if (! $this->db->tableExists('campus')) {
            return;
        }

        $fields = [];
        if (! $this->db->fieldExists('hfz_flag', 'campus')) {
            $fields['hfz_flag'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'a_flag',
            ];
        }
        if (! $this->db->fieldExists('default_sabaq_lines', 'campus')) {
            $fields['default_sabaq_lines'] = [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 5,
                'after'      => 'hfz_flag',
            ];
        }
        if (! $this->db->fieldExists('default_mutalia_lines', 'campus')) {
            $fields['default_mutalia_lines'] = [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 3,
                'after'      => 'default_sabaq_lines',
            ];
        }
        if (! $this->db->fieldExists('hifz_class_id', 'campus')) {
            $fields['hifz_class_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'default_mutalia_lines',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('campus', $fields);
        }
    }

    private function addClassesColumn(): void
    {
        if (! $this->db->tableExists('classes')) {
            return;
        }
        if ($this->db->fieldExists('is_hifz_class', 'classes')) {
            return;
        }

        $field = [
            'type'       => 'TINYINT',
            'constraint' => 1,
            'default'    => 0,
        ];

        if ($this->db->fieldExists('status', 'classes')) {
            $field['after'] = 'status';
        }

        $this->forge->addColumn('classes', [
            'is_hifz_class' => $field,
        ]);
    }

    private function createQuranReferenceTables(): void
    {
        if (! $this->db->tableExists('quran_mushaf_layouts')) {
            $this->forge->addField([
                'layout_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                ],
                'layout_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'lines_per_page' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                ],
                'total_pages' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned'   => true,
                ],
                'total_lines' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
            ]);
            $this->forge->addKey('layout_code', true);
            $this->forge->createTable('quran_mushaf_layouts', true);
        }

        if (! $this->db->tableExists('quran_surahs')) {
            $this->forge->addField([
                'surah_id' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                ],
                'surah_name_en' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'surah_name_ar' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'total_ayahs' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                ],
                'revelation_order' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                ],
            ]);
            $this->forge->addKey('surah_id', true);
            $this->forge->createTable('quran_surahs', true);
        }

        if (! $this->db->tableExists('quran_juz_boundaries')) {
            $this->forge->addField([
                'juz_no' => [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'unsigned'   => true,
                ],
                'start_surah_id' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                ],
                'start_ayah' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                ],
                'end_surah_id' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                ],
                'end_ayah' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                ],
                'total_ayahs' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                ],
                'start_page' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned'   => true,
                ],
                'end_page' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned'   => true,
                ],
                'total_lines' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
            ]);
            $this->forge->addKey('juz_no', true);
            $this->forge->createTable('quran_juz_boundaries', true);
        }

        if (! $this->db->tableExists('quran_mushaf_lines')) {
            $this->forge->addField([
                'layout_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                ],
                'global_line_no' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'page_no' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned'   => true,
                ],
                'line_on_page' => [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'unsigned'   => true,
                ],
                'juz_no' => [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'unsigned'   => true,
                ],
                'surah_id_start' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                ],
                'ayah_start' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                ],
                'surah_id_end' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                ],
                'ayah_end' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                ],
            ]);
            $this->forge->addKey(['layout_code', 'global_line_no'], true);
            $this->forge->addKey(['layout_code', 'page_no', 'line_on_page']);
            $this->forge->addKey(['layout_code', 'juz_no']);
            $this->forge->createTable('quran_mushaf_lines', true);
        }
    }

    private function createHifzOperationalTables(): void
    {
        if (! $this->db->tableExists('hifz_sections')) {
            $this->forge->addField([
                'hifz_sec_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'campus_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'session_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'section_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'sort_order' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'status' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('hifz_sec_id', true);
            $this->forge->addKey(['campus_id', 'session_id', 'status']);
            $this->forge->createTable('hifz_sections', true);
        }

        if (! $this->db->tableExists('hifz_students')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'student_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'campus_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'session_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'hifz_sec_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'memorization_sequence' => [
                    'type'       => 'ENUM',
                    'constraint' => ['para_forward', 'surah_reverse_full', 'surah_reverse_ayah_reverse'],
                    'default'    => 'para_forward',
                ],
                'sabaq_lines_per_day' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'default'    => 5,
                ],
                'mutalia_lines_per_day' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'default'    => 3,
                ],
                'current_global_line' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'current_juz' => [
                    'type'       => 'TINYINT',
                    'constraint' => 2,
                    'unsigned'   => true,
                    'default'    => 1,
                ],
                'current_juz_memorized_lines' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'enrollment_date' => ['type' => 'DATE', 'null' => true],
                'status' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['student_id', 'session_id']);
            $this->forge->addKey(['campus_id', 'hifz_sec_id', 'status']);
            $this->forge->createTable('hifz_students', true);
        }

        if (! $this->db->tableExists('hifz_teacher_sections')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'hifz_sec_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'teacher_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'session_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'campus_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'status' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['hifz_sec_id', 'session_id']);
            $this->forge->createTable('hifz_teacher_sections', true);
        }

        if (! $this->db->tableExists('hifz_daily_recitation')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'student_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'hifz_sec_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'recitation_date' => ['type' => 'DATE'],
                'teacher_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'campus_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'session_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'sabaq_lines_requested' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'sabaq_line_from' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'sabaq_line_to' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'sabaq_surah_id_start' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'sabaq_ayah_from' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'sabaq_surah_id_end' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'sabaq_ayah_to' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'sabaq_quality' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                ],
                'sabaq_remarks' => ['type' => 'TEXT', 'null' => true],
                'sabqi_line_from' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'sabqi_line_to' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'sabqi_auto_generated' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'sabqi_quality' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                ],
                'sabqi_remarks' => ['type' => 'TEXT', 'null' => true],
                'manzil_juz_list' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 60,
                    'null'       => true,
                ],
                'manzil_listener_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => true,
                    'default'    => 'teacher',
                ],
                'manzil_listener_student_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'manzil_hard_mistakes' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'manzil_soft_mistakes' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'mutalia_lines_requested' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'mutalia_surah_id_start' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'mutalia_ayah_from' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'mutalia_surah_id_end' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'mutalia_ayah_to' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'mutalia_remarks' => ['type' => 'TEXT', 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['student_id', 'recitation_date']);
            $this->forge->addKey(['campus_id', 'recitation_date']);
            $this->forge->createTable('hifz_daily_recitation', true);
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHifzRecitationGradingColumns extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('hifz_mutalia_logs')) {
            $this->addMutaliaSabaqColumns();
        }

        if ($this->db->tableExists('hifz_sabqi_logs')) {
            $this->addSabqiColumns();
        }

        if ($this->db->tableExists('hifz_manzil_logs')) {
            $this->addManzilColumns();
        }
    }

    public function down()
    {
        if ($this->db->tableExists('hifz_mutalia_logs')) {
            foreach (['sabaq_hard_mistakes', 'sabaq_soft_mistakes', 'sabaq_listener_type', 'sabaq_listener_student_id'] as $col) {
                if ($this->db->fieldExists($col, 'hifz_mutalia_logs')) {
                    $this->forge->dropColumn('hifz_mutalia_logs', $col);
                }
            }
        }

        if ($this->db->tableExists('hifz_sabqi_logs')) {
            foreach (['sabqi_quality', 'hard_mistakes', 'soft_mistakes', 'listener_type', 'listener_student_id'] as $col) {
                if ($this->db->fieldExists($col, 'hifz_sabqi_logs')) {
                    $this->forge->dropColumn('hifz_sabqi_logs', $col);
                }
            }
        }

        if ($this->db->tableExists('hifz_manzil_logs')) {
            if ($this->db->fieldExists('recitation_quality', 'hifz_manzil_logs')) {
                $this->forge->dropColumn('hifz_manzil_logs', 'recitation_quality');
            }
        }
    }

    protected function addMutaliaSabaqColumns(): void
    {
        $fields = [];
        if (! $this->db->fieldExists('sabaq_hard_mistakes', 'hifz_mutalia_logs')) {
            $fields['sabaq_hard_mistakes'] = [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'default'    => 0,
            ];
        }
        if (! $this->db->fieldExists('sabaq_soft_mistakes', 'hifz_mutalia_logs')) {
            $fields['sabaq_soft_mistakes'] = [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'default'    => 0,
            ];
        }
        if (! $this->db->fieldExists('sabaq_listener_type', 'hifz_mutalia_logs')) {
            $fields['sabaq_listener_type'] = [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => 'teacher',
            ];
        }
        if (! $this->db->fieldExists('sabaq_listener_student_id', 'hifz_mutalia_logs')) {
            $fields['sabaq_listener_student_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
        }
        if ($fields !== []) {
            $this->forge->addColumn('hifz_mutalia_logs', $fields);
        }
    }

    protected function addSabqiColumns(): void
    {
        $fields = [];
        if (! $this->db->fieldExists('sabqi_quality', 'hifz_sabqi_logs')) {
            $fields['sabqi_quality'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ];
        }
        if (! $this->db->fieldExists('hard_mistakes', 'hifz_sabqi_logs')) {
            $fields['hard_mistakes'] = [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'default'    => 0,
            ];
        }
        if (! $this->db->fieldExists('soft_mistakes', 'hifz_sabqi_logs')) {
            $fields['soft_mistakes'] = [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'default'    => 0,
            ];
        }
        if (! $this->db->fieldExists('listener_type', 'hifz_sabqi_logs')) {
            $fields['listener_type'] = [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => 'teacher',
            ];
        }
        if (! $this->db->fieldExists('listener_student_id', 'hifz_sabqi_logs')) {
            $fields['listener_student_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
        }
        if ($fields !== []) {
            $this->forge->addColumn('hifz_sabqi_logs', $fields);
        }
    }

    protected function addManzilColumns(): void
    {
        $fields = [];
        if (! $this->db->fieldExists('recitation_quality', 'hifz_manzil_logs')) {
            $fields['recitation_quality'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ];
        }
        if (! $this->db->fieldExists('remarks', 'hifz_manzil_logs')) {
            $fields['remarks'] = ['type' => 'TEXT', 'null' => true];
        }
        if ($fields !== []) {
            $this->forge->addColumn('hifz_manzil_logs', $fields);
        }
    }
}

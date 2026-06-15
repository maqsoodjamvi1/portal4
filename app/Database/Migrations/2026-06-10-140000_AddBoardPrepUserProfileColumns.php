<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBoardPrepUserProfileColumns extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('board_prep_users')) {
            return;
        }

        $columns = [
            'phone'         => 'father_name',
            'city'          => 'phone',
            'school_name'   => 'city',
            'province'      => 'school_name',
            'country'       => 'province',
            'date_of_birth' => 'country',
            'profile_photo' => 'date_of_birth',
        ];

        $fieldDefs = [
            'phone' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'city' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'school_name' => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'province' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'country' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'date_of_birth' => ['type' => 'DATE', 'null' => true],
            'profile_photo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ];

        foreach ($columns as $name => $after) {
            if ($this->db->fieldExists($name, 'board_prep_users')) {
                continue;
            }
            $def = $fieldDefs[$name];
            if ($this->db->fieldExists($after, 'board_prep_users')) {
                $def['after'] = $after;
            }
            $this->forge->addColumn('board_prep_users', [$name => $def]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('board_prep_users')) {
            return;
        }

        foreach (['profile_photo', 'date_of_birth', 'country', 'province', 'school_name', 'city', 'phone'] as $col) {
            if ($this->db->fieldExists($col, 'board_prep_users')) {
                $this->forge->dropColumn('board_prep_users', $col);
            }
        }
    }
}

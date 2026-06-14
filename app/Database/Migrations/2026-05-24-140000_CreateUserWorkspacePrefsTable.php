<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserWorkspacePrefsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('user_workspace_prefs')) {
            return;
        }

        $this->forge->addField([
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'campus_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'session_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('user_id', true);
        $this->forge->createTable('user_workspace_prefs', true);
    }

    public function down()
    {
        if ($this->db->tableExists('user_workspace_prefs')) {
            $this->forge->dropTable('user_workspace_prefs', true);
        }
    }
}

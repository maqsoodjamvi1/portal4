<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoleMenuAccessTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('role_menu_access')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'menu_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'allowed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'updated_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['role_id', 'menu_key']);
        $this->forge->addKey('role_id');
        $this->forge->createTable('role_menu_access', true);
    }

    public function down()
    {
        $this->forge->dropTable('role_menu_access', true);
    }
}

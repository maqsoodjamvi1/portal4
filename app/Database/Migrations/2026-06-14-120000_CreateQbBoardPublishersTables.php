<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQbBoardPublishersTables extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('qb_board_publishers')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'system_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                ],
                'short_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'null'       => true,
                ],
                'sort_order' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'status' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['system_id', 'status']);
            $this->forge->addUniqueKey(['system_id', 'name']);
            $this->forge->createTable('qb_board_publishers', true);
        }

        if (! $this->db->tableExists('qb_topic_board_publishers')) {
            $this->forge->addField([
                'topic_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'board_publisher_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
            ]);
            $this->forge->addKey(['topic_id', 'board_publisher_id'], true);
            $this->forge->addKey('board_publisher_id');
            $this->forge->createTable('qb_topic_board_publishers', true);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('qb_topic_board_publishers', true);
        $this->forge->dropTable('qb_board_publishers', true);
    }
}

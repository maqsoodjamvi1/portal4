<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQbBoardPublisherLogoColumn extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('qb_board_publishers')) {
            return;
        }

        if (! $this->db->fieldExists('logo', 'qb_board_publishers')) {
            $this->forge->addColumn('qb_board_publishers', [
                'logo' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'short_code',
                ],
            ]);
        }

        // All boards/publishers are global (platform-wide), not per school system.
        $this->db->table('qb_board_publishers')
            ->where('system_id !=', 0)
            ->update(['system_id' => 0]);
    }

    public function down(): void
    {
        if ($this->db->tableExists('qb_board_publishers')
            && $this->db->fieldExists('logo', 'qb_board_publishers')) {
            $this->forge->dropColumn('qb_board_publishers', 'logo');
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuranAyahsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('quran_ayahs')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'surah_id' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
            ],
            'ayah_no' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
            ],
            'text_ar' => [
                'type' => 'TEXT',
            ],
            'script_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'uthmani',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['surah_id', 'ayah_no']);
        $this->forge->createTable('quran_ayahs', true);
    }

    public function down()
    {
        $this->forge->dropTable('quran_ayahs', true);
    }
}

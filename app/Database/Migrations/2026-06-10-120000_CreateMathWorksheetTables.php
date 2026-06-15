<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMathWorksheetTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'campus_id'     => ['type' => 'INT', 'unsigned' => true],
            'title'         => ['type' => 'VARCHAR', 'constraint' => 120],
            'grade'         => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'layout'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'horizontal'],
            'problem_count' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'settings_json' => ['type' => 'TEXT', 'null' => true],
            'student_name'  => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'created_by'    => ['type' => 'INT', 'unsigned' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('campus_id');
        $this->forge->createTable('math_worksheet_sets', true);

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'set_id'        => ['type' => 'INT', 'unsigned' => true],
            'problems_json' => ['type' => 'MEDIUMTEXT'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('set_id');
        $this->forge->createTable('math_worksheet_problems', true);
    }

    public function down()
    {
        $this->forge->dropTable('math_worksheet_problems', true);
        $this->forge->dropTable('math_worksheet_sets', true);
    }
}

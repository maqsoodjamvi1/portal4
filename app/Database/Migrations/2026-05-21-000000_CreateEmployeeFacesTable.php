<?php



namespace App\Database\Migrations;



use CodeIgniter\Database\Migration;



class CreateEmployeeFacesTable extends Migration

{

    public function up(): void

    {

        $this->forge->addField([

            'id' => [

                'type'           => 'INT',

                'constraint'     => 11,

                'unsigned'       => true,

                'auto_increment' => true,

            ],

            'emp_id' => [

                'type'       => 'INT',

                'constraint' => 11,

                'unsigned'   => true,

            ],

            'face_id' => [

                'type'       => 'VARCHAR',

                'constraint' => 64,

            ],

            'campus_id' => [

                'type'       => 'INT',

                'constraint' => 11,

                'unsigned'   => true,

            ],

            'image_path' => [

                'type'       => 'VARCHAR',

                'constraint' => 255,

                'null'       => true,

            ],

            'created_date' => [

                'type' => 'DATETIME',

                'null' => true,

            ],

            'user_id' => [

                'type'       => 'INT',

                'constraint' => 11,

                'unsigned'   => true,

                'null'       => true,

            ],

        ]);



        $this->forge->addKey('id', true);

        $this->forge->addKey(['campus_id', 'emp_id']);

        $this->forge->addUniqueKey('face_id');

        $this->forge->createTable('employee_faces', true);

    }



    public function down(): void

    {

        $this->forge->dropTable('employee_faces', true);

    }

}

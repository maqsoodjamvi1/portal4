<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDescriptionToQbTopics extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('qb_topics')) {
            return;
        }

        if ($this->db->fieldExists('description', 'qb_topics')) {
            return;
        }

        $this->forge->addColumn('qb_topics', [
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'topic_name',
            ],
        ]);
    }

    public function down()
    {
        if (! $this->db->tableExists('qb_topics')) {
            return;
        }

        if ($this->db->fieldExists('description', 'qb_topics')) {
            $this->forge->dropColumn('qb_topics', 'description');
        }
    }
}

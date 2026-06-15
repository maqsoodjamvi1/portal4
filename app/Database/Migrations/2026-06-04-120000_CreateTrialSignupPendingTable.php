<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrialSignupPendingTable extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('trial_signup_pending')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 254,
            ],
            'school_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'phone_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'otp_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'otp_expires_at' => [
                'type' => 'DATETIME',
            ],
            'verify_attempts' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'resend_count' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'last_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token');
        $this->forge->addKey('email');
        $this->forge->addKey('otp_expires_at');
        $this->forge->createTable('trial_signup_pending', true);
    }

    public function down(): void
    {
        if ($this->db->tableExists('trial_signup_pending')) {
            $this->forge->dropTable('trial_signup_pending', true);
        }
    }
}

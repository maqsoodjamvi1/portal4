<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * RBAC tables used by MemberAcl, Roles, Permissions, and Users.
 * Uses IF NOT EXISTS so existing legacy databases are not altered.
 */
class CreateRbacTables extends Migration
{
    public function up(): void
    {
        // role_name — canonical role labels (hierarchical)
        if (! $this->db->tableExists('role_name')) {
            $this->forge->addField([
                'role_name_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'rolename' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 128,
                ],
                'detail' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'parent_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
            ]);
            $this->forge->addKey('role_name_id', true);
            $this->forge->addKey('parent_id');
            $this->forge->createTable('role_name', true);
        }

        // permissions — feature keys (tree)
        if (! $this->db->tableExists('permissions')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'permKey' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 128,
                ],
                'permName' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'parent_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'lft' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'rgt' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'root_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'sortid' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'issys' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'permType' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'rel_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'created_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('permKey');
            $this->forge->addKey('parent_id');
            $this->forge->createTable('permissions', true);
        }

        // roles — role instance per system plan
        if (! $this->db->tableExists('roles')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'role_name_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'plan_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'issys' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['role_name_id', 'plan_id']);
            $this->forge->createTable('roles', true);
        }

        // role_perms — permissions granted to a role
        if (! $this->db->tableExists('role_perms')) {
            $this->forge->addField([
                'ID' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'roleID' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'permID' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'value' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'add_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('ID', true);
            $this->forge->addKey(['roleID', 'permID']);
            $this->forge->createTable('role_perms', true);
        }

        // user_roles — assign roles to staff users
        if (! $this->db->tableExists('user_roles')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'userID' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'roleID' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'addDate' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['userID', 'roleID']);
            $this->forge->createTable('user_roles', true);
        }

        // user_perms — per-user permission overrides
        if (! $this->db->tableExists('user_perms')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'userID' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'permID' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'value' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'addDate' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['userID', 'permID']);
            $this->forge->createTable('user_perms', true);
        }

        // role_classes — optional class/section scope for a role
        if (! $this->db->tableExists('role_classes')) {
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
                'class_section_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['role_id', 'class_section_id']);
            $this->forge->createTable('role_classes', true);
        }
    }

    public function down(): void
    {
        // Do not drop legacy production tables automatically.
    }
}

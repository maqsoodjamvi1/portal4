<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Campus finance ledger: accounts, transactions, petty cash links.
 */
class CreateCampusFinanceTables extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('campus_finance_settings')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'enable_user_petty_cash' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('campus_id');
            $this->forge->createTable('campus_finance_settings', true);
        }

        if (! $this->db->tableExists('campus_finance_accounts')) {
            $this->forge->addField([
                'account_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'account_type' => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'cash'],
                'account_name' => ['type' => 'VARCHAR', 'constraint' => 128],
                'account_number' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'is_campus_cash' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'opening_balance' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('account_id', true);
            $this->forge->addKey(['campus_id', 'is_active']);
            $this->forge->createTable('campus_finance_accounts', true);
        }

        if (! $this->db->tableExists('user_finance_accounts')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'account_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['campus_id', 'user_id']);
            $this->forge->createTable('user_finance_accounts', true);
        }

        if (! $this->db->tableExists('finance_transactions')) {
            $this->forge->addField([
                'transaction_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'transaction_type' => ['type' => 'VARCHAR', 'constraint' => 32],
                'direction' => ['type' => 'VARCHAR', 'constraint' => 8],
                'amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'account_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'received_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'transaction_date' => ['type' => 'DATE', 'null' => true],
                'reference_type' => ['type' => 'VARCHAR', 'constraint' => 48, 'null' => true],
                'reference_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'notes' => ['type' => 'TEXT', 'null' => true],
                'is_reversed' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'reversal_of' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('transaction_id', true);
            $this->forge->addKey(['campus_id', 'transaction_date']);
            $this->forge->addKey(['account_id', 'transaction_date']);
            $this->forge->createTable('finance_transactions', true);
        }

        if (! $this->db->tableExists('finance_transaction_items')) {
            $this->forge->addField([
                'item_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'transaction_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'chalan_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'discount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            ]);
            $this->forge->addKey('item_id', true);
            $this->forge->addKey('transaction_id');
            $this->forge->addKey('chalan_id');
            $this->forge->createTable('finance_transaction_items', true);
        }

        $this->addColumnIfMissing('fee_chalan', 'finance_transaction_id', [
            'type'       => 'INT',
            'constraint' => 11,
            'unsigned'   => true,
            'null'       => true,
        ]);
        $this->addColumnIfMissing('fee_chalan', 'collection_account_id', [
            'type'       => 'INT',
            'constraint' => 11,
            'unsigned'   => true,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('expenses', 'account_id', [
            'type'       => 'INT',
            'constraint' => 11,
            'unsigned'   => true,
            'null'       => true,
        ]);
        $this->addColumnIfMissing('expenses', 'expense_date', [
            'type' => 'DATE',
            'null' => true,
        ]);
        $this->addColumnIfMissing('expenses', 'finance_transaction_id', [
            'type'       => 'INT',
            'constraint' => 11,
            'unsigned'   => true,
            'null'       => true,
        ]);

        if ($this->db->tableExists('salary_slips')) {
            $this->addColumnIfMissing('salary_slips', 'paid_from_account_id', [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ]);
            $this->addColumnIfMissing('salary_slips', 'finance_transaction_id', [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ]);
        }
    }

    public function down(): void
    {
        $tables = [
            'finance_transaction_items',
            'finance_transactions',
            'user_finance_accounts',
            'campus_finance_accounts',
            'campus_finance_settings',
        ];
        foreach ($tables as $t) {
            if ($this->db->tableExists($t)) {
                $this->forge->dropTable($t, true);
            }
        }

        foreach (['fee_chalan', 'expenses', 'salary_slips'] as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }
            foreach (['finance_transaction_id', 'collection_account_id', 'account_id', 'expense_date', 'paid_from_account_id'] as $col) {
                if ($this->db->fieldExists($col, $table)) {
                    $this->forge->dropColumn($table, $col);
                }
            }
        }
    }

    private function addColumnIfMissing(string $table, string $column, array $def): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }
        if ($this->db->fieldExists($column, $table)) {
            return;
        }
        $this->forge->addColumn($table, [$column => $def]);
    }
}

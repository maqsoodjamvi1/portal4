<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use Config\Hifz as HifzConfig;
use Throwable;

/**
 * Applies Hifz database schema on the live server without CLI (php spark migrate).
 */
class HifzSchemaEnsurer
{
    private static bool $ranThisRequest = false;

    protected BaseConnection $db;
    protected $forge;
    protected HifzConfig $config;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db     = $db ?? Database::connect();
        $this->forge  = Database::forge($this->db);
        $this->config = config(HifzConfig::class);
    }

    public function ensure(): void
    {
        if (self::$ranThisRequest) {
            return;
        }

        self::$ranThisRequest = true;

        if (! $this->config->autoMigrate && ! $this->config->autoEnsureColumns) {
            return;
        }

        if ($this->config->autoMigrate && $this->shouldRunMigrations()) {
            $this->runPendingMigrations();
        }

        if ($this->config->autoEnsureColumns) {
            $this->ensureParaOnlySchema();
            $this->ensureHifzColumns();
        }
    }

    protected function ensureParaOnlySchema(): void
    {
        if ($this->db->tableExists('hifz_sections')) {
            $this->addColumnIfMissing('hifz_sections', 'sort_order', [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'default'    => 0,
            ]);
        }

        if (! $this->db->tableExists('hifz_students')) {
            return;
        }

        $this->addColumnIfMissing('hifz_students', 'current_para_no', [
            'type'       => 'TINYINT',
            'constraint' => 2,
            'unsigned'   => true,
            'default'    => 1,
        ]);

        $this->addColumnIfMissing('hifz_students', 'sabqi_active_paras', [
            'type'       => 'VARCHAR',
            'constraint' => 120,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('hifz_students', 'manzil_pool_paras', [
            'type'       => 'VARCHAR',
            'constraint' => 120,
            'null'       => true,
        ]);

        $this->ensureMemorizationSequenceColumn();
        $this->ensureMutaliaLogsTable();
        $this->ensureLessonLogColumns();
        $this->ensureSabqiLogsTables();
        $this->ensureManzilLogsTable();
    }

    /**
     * Allow para_reverse (UI uses para_forward / para_reverse; legacy ENUM had surah_* only).
     */
    protected function ensureMemorizationSequenceColumn(): void
    {
        if (! $this->db->tableExists('hifz_students')) {
            return;
        }

        try {
            $row = $this->db->query(
                "SHOW COLUMNS FROM `hifz_students` WHERE Field = 'memorization_sequence'"
            )->getRow();

            if (! $row) {
                return;
            }

            $type = strtolower((string) ($row->Type ?? ''));

            if (str_contains($type, 'enum') && ! str_contains($type, 'para_reverse')) {
                $this->db->query(
                    "ALTER TABLE `hifz_students` MODIFY `memorization_sequence` VARCHAR(32) NOT NULL DEFAULT 'para_forward'"
                );

                $this->db->query(
                    "UPDATE `hifz_students` SET `memorization_sequence` = 'para_reverse'
                     WHERE `memorization_sequence` IN ('surah_reverse_full', 'surah_reverse_ayah_reverse')"
                );
            }
        } catch (Throwable $e) {
            log_message('error', 'HifzSchemaEnsurer memorization_sequence: ' . $e->getMessage());
        }
    }

    protected function ensureLessonLogColumns(): void
    {
        if (! $this->db->tableExists('hifz_mutalia_logs')) {
            return;
        }

        $this->addColumnIfMissing('hifz_mutalia_logs', 'sabaq_date', ['type' => 'DATE', 'null' => true]);
        $this->addColumnIfMissing('hifz_mutalia_logs', 'sabaq_quality', [
            'type'       => 'VARCHAR',
            'constraint' => 20,
            'null'       => true,
        ]);
        $this->addColumnIfMissing('hifz_mutalia_logs', 'sabaq_remarks', ['type' => 'TEXT', 'null' => true]);
        $this->addColumnIfMissing('hifz_mutalia_logs', 'line_from', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'null'       => true,
        ]);
        $this->addColumnIfMissing('hifz_mutalia_logs', 'line_to', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'null'       => true,
        ]);
        $this->addColumnIfMissing('hifz_mutalia_logs', 'sabaq_hard_mistakes', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'default'    => 0,
        ]);
        $this->addColumnIfMissing('hifz_mutalia_logs', 'sabaq_soft_mistakes', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'default'    => 0,
        ]);
        $this->addColumnIfMissing('hifz_mutalia_logs', 'sabaq_listener_type', [
            'type'       => 'VARCHAR',
            'constraint' => 10,
            'null'       => true,
            'default'    => 'teacher',
        ]);
        $this->addColumnIfMissing('hifz_mutalia_logs', 'sabaq_listener_student_id', [
            'type'       => 'INT',
            'constraint' => 11,
            'unsigned'   => true,
            'null'       => true,
        ]);
    }

    protected function ensureMutaliaLogsTable(): void
    {
        if ($this->db->tableExists('hifz_mutalia_logs')) {
            $this->ensureLessonLogColumns();

            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'session_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'hifz_sec_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'teacher_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'entry_date' => ['type' => 'DATE'],
            'para_no' => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true],
            'lines_count' => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true, 'default' => 0],
            'line_from' => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true, 'null' => true],
            'line_to' => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true, 'null' => true],
            'para_completed' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'new_para_started' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'new_para_no' => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'null' => true],
            'remarks' => ['type' => 'TEXT', 'null' => true],
            'sabaq_date' => ['type' => 'DATE', 'null' => true],
            'sabaq_quality' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'sabaq_remarks' => ['type' => 'TEXT', 'null' => true],
            'created_date' => ['type' => 'DATETIME', 'null' => true],
            'updated_date' => ['type' => 'DATETIME', 'null' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['student_id', 'entry_date']);
        $this->forge->createTable('hifz_mutalia_logs', true);
    }

    protected function ensureSabqiLogsTables(): void
    {
        if (! $this->db->tableExists('hifz_sabqi_logs')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'session_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'hifz_sec_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'teacher_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'entry_date' => ['type' => 'DATE'],
                'remarks' => ['type' => 'TEXT', 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['student_id', 'entry_date']);
            $this->forge->createTable('hifz_sabqi_logs', true);
        }

        if ($this->db->tableExists('hifz_sabqi_logs')) {
            $this->addColumnIfMissing('hifz_sabqi_logs', 'sabqi_quality', [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ]);
            $this->addColumnIfMissing('hifz_sabqi_logs', 'hard_mistakes', [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'default'    => 0,
            ]);
            $this->addColumnIfMissing('hifz_sabqi_logs', 'soft_mistakes', [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'default'    => 0,
            ]);
            $this->addColumnIfMissing('hifz_sabqi_logs', 'listener_type', [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => 'teacher',
            ]);
            $this->addColumnIfMissing('hifz_sabqi_logs', 'listener_student_id', [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ]);
        }

        if (! $this->db->tableExists('hifz_sabqi_log_paras')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'sabqi_log_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'para_no' => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('sabqi_log_id');
            $this->forge->addUniqueKey(['sabqi_log_id', 'para_no']);
            $this->forge->createTable('hifz_sabqi_log_paras', true);
        }
    }

    protected function ensureManzilLogsTable(): void
    {
        if ($this->db->tableExists('hifz_manzil_logs')) {
            $this->addColumnIfMissing('hifz_manzil_logs', 'session_id', [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ]);
            $this->addColumnIfMissing('hifz_manzil_logs', 'campus_id', [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ]);
            $this->addColumnIfMissing('hifz_manzil_logs', 'hifz_sec_id', [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ]);
            $this->addColumnIfMissing('hifz_manzil_logs', 'recitation_quality', [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ]);
            $this->addColumnIfMissing('hifz_manzil_logs', 'remarks', ['type' => 'TEXT', 'null' => true]);

            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'session_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'hifz_sec_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'teacher_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'entry_date' => ['type' => 'DATE'],
            'para_no' => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true],
            'listener_type' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'default' => 'teacher'],
            'listener_student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'hard_mistakes' => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true, 'default' => 0],
            'soft_mistakes' => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true, 'default' => 0],
            'created_date' => ['type' => 'DATETIME', 'null' => true],
            'updated_date' => ['type' => 'DATETIME', 'null' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['student_id', 'entry_date']);
        $this->forge->addUniqueKey(['student_id', 'entry_date', 'para_no']);
        $this->forge->createTable('hifz_manzil_logs', true);
    }

    protected function shouldRunMigrations(): bool
    {
        if (! $this->db->tableExists('hifz_mutalia_logs')) {
            return true;
        }

        if (! $this->db->tableExists('hifz_daily_recitation')) {
            return true;
        }

        $cache = cache();
        $key   = 'hifz_auto_migrate_last';
        $last  = (int) ($cache->get($key) ?? 0);

        if ($last > 0 && (time() - $last) < $this->config->migrateThrottleSeconds) {
            return false;
        }

        return true;
    }

    protected function runPendingMigrations(): void
    {
        try {
            $runner = service('migrations');
            $runner->setNamespace('App');
            $runner->latest();

            cache()->save('hifz_auto_migrate_last', time(), $this->config->migrateThrottleSeconds);
        } catch (Throwable $e) {
            log_message('error', '[HifzSchema] migrate: ' . $e->getMessage());
        }
    }

    protected function ensureHifzColumns(): void
    {
        if (! $this->db->tableExists('hifz_daily_recitation')) {
            return;
        }

        $this->addColumnIfMissing('hifz_daily_recitation', 'mutalia_surah_id_start', [
            'type'       => 'TINYINT',
            'constraint' => 3,
            'unsigned'   => true,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('hifz_daily_recitation', 'mutalia_ayah_from', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('hifz_daily_recitation', 'mutalia_surah_id_end', [
            'type'       => 'TINYINT',
            'constraint' => 3,
            'unsigned'   => true,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('hifz_daily_recitation', 'mutalia_ayah_to', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('hifz_daily_recitation', 'manzil_juz_list', [
            'type'       => 'VARCHAR',
            'constraint' => 60,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('hifz_daily_recitation', 'manzil_listener_type', [
            'type'       => 'VARCHAR',
            'constraint' => 10,
            'null'       => true,
            'default'    => 'teacher',
        ]);

        $this->addColumnIfMissing('hifz_daily_recitation', 'manzil_listener_student_id', [
            'type'       => 'INT',
            'constraint' => 11,
            'unsigned'   => true,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('hifz_daily_recitation', 'manzil_hard_mistakes', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'default'    => 0,
        ]);

        $this->addColumnIfMissing('hifz_daily_recitation', 'manzil_soft_mistakes', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'default'    => 0,
        ]);

        if (! $this->db->tableExists('hifz_students')) {
            return;
        }

        $this->addColumnIfMissing('hifz_students', 'reverse_learned_from_surah', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('hifz_students', 'current_sabaq_surah_id', [
            'type'       => 'TINYINT',
            'constraint' => 3,
            'unsigned'   => true,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('hifz_students', 'current_sabaq_ayah', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'null'       => true,
        ]);

        $this->addColumnIfMissing('hifz_students', 'completed_juz_list', [
            'type' => 'TEXT',
            'null' => true,
        ]);

        $this->addColumnIfMissing('hifz_students', 'manzil_paras_per_day', [
            'type'       => 'TINYINT',
            'constraint' => 2,
            'unsigned'   => true,
            'default'    => 1,
        ]);

        $this->addColumnIfMissing('hifz_students', 'manzil_rotation_index', [
            'type'       => 'SMALLINT',
            'constraint' => 4,
            'unsigned'   => true,
            'default'    => 0,
        ]);
    }

    /**
     * @param array<string, mixed> $definition
     */
    protected function addColumnIfMissing(string $table, string $column, array $definition): void
    {
        if ($this->db->fieldExists($column, $table)) {
            return;
        }

        try {
            $this->forge->addColumn($table, [$column => $definition]);
            log_message('info', '[HifzSchema] Added column ' . $table . '.' . $column);
        } catch (Throwable $e) {
            log_message('error', '[HifzSchema] addColumn ' . $table . '.' . $column . ': ' . $e->getMessage());
        }
    }
}

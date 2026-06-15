<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Migrate school_timings: add campus_id, dedupe rows, add unique index.
 *
 * Usage:
 *   php spark fix:school-timings
 *   php spark fix:school-timings --dry-run
 */
class FixSchoolTimings extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'fix:school-timings';
    protected $description = 'Add campus_id to school_timings, remove duplicates, add unique index';
    protected $usage       = 'fix:school-timings [--dry-run]';

    public function run(array $params): void
    {
        helper('school');

        $dryRun = CLI::getOption('dry-run') !== null;
        $db     = db_connect();

        if (! $db->tableExists('school_timings')) {
            CLI::error('Table school_timings does not exist.');
            return;
        }

        $pkColumn = $this->detectPrimaryKey($db);
        CLI::write('Primary key column: ' . ($pkColumn ?? '(none — using composite delete)'), 'cyan');

        if ($dryRun) {
            CLI::write('DRY RUN — no changes will be written.', 'yellow');
        }

        $stats = [
            'campus_column_added'  => false,
            'campus_backfilled'    => 0,
            'orphans_deleted'      => 0,
            'duplicates_removed'   => 0,
            'unique_index_added'   => false,
        ];

        if (! $dryRun) {
            $db->transBegin();
        }

        try {
            if (! $this->columnExists($db, 'school_timings', 'campus_id')) {
                CLI::write('Adding campus_id column…');
                if (! $dryRun) {
                    $db->query('ALTER TABLE school_timings ADD COLUMN campus_id INT UNSIGNED NULL DEFAULT NULL AFTER cls_sec_id');
                    $stats['campus_column_added'] = true;
                }
            } else {
                CLI::write('campus_id column already exists.', 'green');
            }

            if ($db->tableExists('class_section')) {
                CLI::write('Backfilling campus_id from class_section…');
                if (! $dryRun) {
                    $db->query(
                        'UPDATE school_timings st
                         INNER JOIN class_section cs ON cs.cls_sec_id = st.cls_sec_id
                         SET st.campus_id = cs.campus_id
                         WHERE st.campus_id IS NULL OR st.campus_id = 0'
                    );
                    $stats['campus_backfilled'] = $db->affectedRows();
                }
            }

            CLI::write('Removing rows with invalid cls_sec_id…');
            if (! $dryRun && $db->tableExists('class_section')) {
                $db->query(
                    'DELETE st FROM school_timings st
                     LEFT JOIN class_section cs ON cs.cls_sec_id = st.cls_sec_id
                     WHERE cs.cls_sec_id IS NULL'
                );
                $stats['orphans_deleted'] = $db->affectedRows();
            }

            CLI::write('Deduplicating (cls_sec_id, dayname)…');
            $stats['duplicates_removed'] = $this->deduplicate($db, $pkColumn, $dryRun);

            if (! $this->indexExists($db, 'school_timings', 'uq_timing_section_day')) {
                CLI::write('Adding unique index uq_timing_section_day…');
                if (! $dryRun) {
                    $db->query(
                        'ALTER TABLE school_timings ADD UNIQUE KEY uq_timing_section_day (cls_sec_id, dayname)'
                    );
                    $stats['unique_index_added'] = true;
                }
            } else {
                CLI::write('Unique index uq_timing_section_day already exists.', 'green');
            }

            if (! $this->indexExists($db, 'school_timings', 'idx_school_timings_campus')) {
                CLI::write('Adding index idx_school_timings_campus…');
                if (! $dryRun) {
                    $db->query(
                        'ALTER TABLE school_timings ADD KEY idx_school_timings_campus (campus_id)'
                    );
                }
            }

            if (! $dryRun) {
                if ($db->transStatus() === false) {
                    $db->transRollback();
                    CLI::error('Transaction failed — rolled back.');
                    return;
                }
                $db->transCommit();
            }
        } catch (\Throwable $e) {
            if (! $dryRun) {
                $db->transRollback();
            }
            CLI::error($e->getMessage());
            return;
        }

        CLI::newLine();
        CLI::write('Summary:', 'green');
        foreach ($stats as $key => $val) {
            CLI::write('  ' . $key . ': ' . (is_bool($val) ? ($val ? 'yes' : 'no') : $val));
        }

        $remaining = $db->query(
            'SELECT cls_sec_id, dayname, COUNT(*) AS c
             FROM school_timings
             GROUP BY cls_sec_id, dayname
             HAVING c > 1'
        )->getResultArray();

        if ($remaining !== []) {
            CLI::error('Duplicates still remain: ' . count($remaining) . ' groups');
        } else {
            CLI::write('No duplicate (cls_sec_id, dayname) groups remain.', 'green');
        }
    }

    private function detectPrimaryKey($db): ?string
    {
        $fields = $db->getFieldData('school_timings');
        foreach ($fields as $field) {
            if (! empty($field->primary_key)) {
                return $field->name;
            }
        }

        foreach (['id', 'st_id', 'school_timing_id', 'timing_id'] as $candidate) {
            if ($this->columnExists($db, 'school_timings', $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function columnExists($db, string $table, string $column): bool
    {
        return in_array($column, $db->getFieldNames($table), true);
    }

    private function indexExists($db, string $table, string $indexName): bool
    {
        $rows = $db->query('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$indexName])->getResultArray();

        return $rows !== [];
    }

    private function deduplicate($db, ?string $pkColumn, bool $dryRun): int
    {
        $groups = $db->query(
            'SELECT cls_sec_id, dayname, COUNT(*) AS c
             FROM school_timings
             GROUP BY cls_sec_id, dayname
             HAVING c > 1'
        )->getResultArray();

        if ($groups === []) {
            return 0;
        }

        $removed = 0;

        foreach ($groups as $group) {
            $clsSecId = (int) $group['cls_sec_id'];
            $dayname  = (string) $group['dayname'];

            $rows = $db->table('school_timings')
                ->where('cls_sec_id', $clsSecId)
                ->where('dayname', $dayname)
                ->get()
                ->getResultArray();

            if (count($rows) <= 1) {
                continue;
            }

            $keepId = $this->pickRowToKeep($rows, $pkColumn);

            foreach ($rows as $row) {
                $rowId = $pkColumn !== null ? (int) ($row[$pkColumn] ?? 0) : 0;
                if ($pkColumn !== null && $rowId === $keepId) {
                    continue;
                }

                if ($pkColumn !== null && $rowId > 0) {
                    if (! $dryRun) {
                        $db->table('school_timings')->where($pkColumn, $rowId)->delete();
                    }
                    $removed++;
                    continue;
                }

                if (! $dryRun) {
                    $db->table('school_timings')
                        ->where('cls_sec_id', $clsSecId)
                        ->where('dayname', $dayname)
                        ->where('checkin_timing', $row['checkin_timing'] ?? '')
                        ->where('checkout_timing', $row['checkout_timing'] ?? '')
                        ->limit(1)
                        ->delete();
                }
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function pickRowToKeep(array $rows, ?string $pkColumn): int
    {
        usort($rows, static function (array $a, array $b) use ($pkColumn): int {
            $aWorking = isSchoolTimingWorkingDay($a['checkin_timing'] ?? null, $a['checkout_timing'] ?? null) ? 1 : 0;
            $bWorking = isSchoolTimingWorkingDay($b['checkin_timing'] ?? null, $b['checkout_timing'] ?? null) ? 1 : 0;
            if ($aWorking !== $bWorking) {
                return $bWorking <=> $aWorking;
            }

            if ($pkColumn !== null) {
                return ((int) ($b[$pkColumn] ?? 0)) <=> ((int) ($a[$pkColumn] ?? 0));
            }

            return 0;
        });

        if ($pkColumn === null) {
            return 0;
        }

        return (int) ($rows[0][$pkColumn] ?? 0);
    }
}

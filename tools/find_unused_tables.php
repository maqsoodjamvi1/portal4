<?php
/**
 * Find unused / backup / temporary database tables.
 * Usage: php tools/find_unused_tables.php [--delete]
 */
$root = dirname(__DIR__);

// Load .env manually
$envFile = $root . '/.env';
$env = [];
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v, " \t\"'");
        }
    }
}

$host = $env['database.default.hostname'] ?? 'localhost';
$port = (int) ($env['database.default.port'] ?? 3306);
$dbName = $env['database.default.database'] ?? '';
$user = $env['database.default.username'] ?? 'root';
$pass = $env['database.default.password'] ?? '';

if ($dbName === '') {
    fwrite(STDERR, "No database name in .env\n");
    exit(1);
}

$mysqli = @new mysqli($host, $user, $pass, $dbName, $port);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB connect failed: {$mysqli->connect_error}\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$res = $mysqli->query('SHOW TABLES');
$allTables = [];
while ($row = $res->fetch_array()) {
    $allTables[] = $row[0];
}
sort($allTables);

$backupPattern = '/(?:backup|_bak\b|_old\b|_copy\b|_tmp\b|_temp\b|temp_|tmp_|copy_|old_|bak_|\d{4}[-_]\d{2}[-_]\d{2}|\d{1,2}[-_]\d{1,2}[-_]\d{2,4}|\d{1,2}(?:st|nd|rd|th)?\s*(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)|_\d{8}$|_\d{6}$)/i';

function isProtectedTable(string $table): bool
{
    static $protected = [
        'migrations', 'ci_sessions', 'sessions',
    ];
    return in_array(strtolower($table), $protected, true);
}

function collectPhpFiles(string $dir): array
{
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $path = str_replace('\\', '/', $file->getPathname());
            if (str_contains($path, '/vendor/') || str_contains($path, '/node_modules/')) {
                continue;
            }
            $files[] = $path;
        }
    }
    return $files;
}

$sourceFiles = collectPhpFiles($root . '/app');
$sourceFiles[] = $root . '/app/Config/Routes.php';

$referenced = [];
foreach ($sourceFiles as $file) {
    $content = @file_get_contents($file);
    if ($content === false) {
        continue;
    }
    foreach ($allTables as $table) {
        // word boundary style match for table names in SQL/PHP
        if (preg_match('/[`\'"]?' . preg_quote($table, '/') . '[`\'"]?/i', $content)) {
            $referenced[$table] = true;
        }
    }
}

$unused = [];
$backupUnused = [];
$backupUsed = [];
$used = [];

foreach ($allTables as $table) {
    if (isProtectedTable($table)) {
        $used[] = $table;
        continue;
    }
    $isBackup = (bool) preg_match($backupPattern, $table);
    if (isset($referenced[$table])) {
        $used[] = $table;
        if ($isBackup) {
            $backupUsed[] = $table;
        }
    } else {
        $unused[] = $table;
        if ($isBackup) {
            $backupUnused[] = $table;
        }
    }
}

$delete = in_array('--delete', $argv ?? [], true);
$report = $root . '/tools/unused_tables_report.txt';

$lines = [
    'Database: ' . $dbName,
    'Total tables: ' . count($allTables),
    'Referenced in app code: ' . count($used),
    'Unused (no code reference): ' . count($unused),
    'Unused backup/temp/dated: ' . count($backupUnused),
    '',
    '=== BACKUP/TEMP/DATED — UNUSED (safest to drop) ===',
];
foreach ($backupUnused as $t) {
    $lines[] = $t;
}
$lines[] = '';
$lines[] = '=== OTHER UNUSED (review before drop) ===';
foreach ($unused as $t) {
    if (!preg_match($backupPattern, $t)) {
        $lines[] = $t;
    }
}
$lines[] = '';
$lines[] = '=== BACKUP/TEMP/DATED BUT STILL REFERENCED IN CODE (keep or refactor first) ===';
foreach ($backupUsed as $t) {
    $lines[] = $t;
}

file_put_contents($report, implode(PHP_EOL, $lines) . PHP_EOL);

echo implode(PHP_EOL, array_slice($lines, 0, 6)) . PHP_EOL;
echo 'Report: tools/unused_tables_report.txt' . PHP_EOL;

if ($delete) {
    $toDrop = array_merge($backupUnused, array_values(array_filter($unused, fn($t) => !preg_match($backupPattern, $t))));
    // Only auto-drop clearly backup/temp/dated unused tables unless --delete-all passed
    $dropAllUnused = in_array('--delete-all', $argv ?? [], true);
    $targets = $dropAllUnused ? $unused : $backupUnused;

    $mysqli->query('SET FOREIGN_KEY_CHECKS = 0');

    $dropped = 0;
    $failed = 0;
    foreach ($targets as $table) {
        if (isProtectedTable($table)) {
            continue;
        }
        $sql = 'DROP TABLE IF EXISTS `' . $mysqli->real_escape_string($table) . '`';
        if ($mysqli->query($sql)) {
            $dropped++;
            echo "Dropped: {$table}\n";
        } else {
            $failed++;
            echo "Failed: {$table} — {$mysqli->error}\n";
        }
    }

    $mysqli->query('SET FOREIGN_KEY_CHECKS = 1');
    echo "Dropped: {$dropped}, Failed: {$failed}\n";
}

$mysqli->close();

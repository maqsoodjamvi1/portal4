<?php
/**
 * Deploy database changes to LIVE server.
 *
 * STRUCTURE (no live data touched except new empty tables / new columns):
 *   php tools/deploy_live_schema.php migrate
 *
 * REMOVE unused backup tables (same as local cleanup):
 *   php tools/deploy_live_schema.php drop-unused
 *
 * QURAN reference DATA only (surahs, juz, mushaf lines, ayah text):
 *   php tools/deploy_live_schema.php seed-quran
 *
 * FULL deploy (recommended order):
 *   php tools/deploy_live_schema.php all
 *
 * Dry run: append --dry-run to any command
 */
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

$action = $argv[1] ?? 'help';
$dryRun = in_array('--dry-run', $argv, true);

function out(string $msg): void
{
    echo $msg . PHP_EOL;
}

function loadEnv(string $root): array
{
    $env = [];
    $file = $root . '/.env';
    if (! is_file($file)) {
        return $env;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $v = trim($v, " \t\"'");
            if (($hash = strpos($v, '#')) !== false) {
                $v = trim(substr($v, 0, $hash));
            }
            $env[trim($k)] = $v;
        }
    }

    return $env;
}

function dbConnect(array $env): mysqli
{
    $host = $env['database.default.hostname'] ?? 'localhost';
    $port = (int) ($env['database.default.port'] ?? 3306);
    $db   = $env['database.default.database'] ?? '';
    $user = $env['database.default.username'] ?? 'root';
    $pass = $env['database.default.password'] ?? '';

    if ($db === '') {
        throw new RuntimeException('database.default.database not set in .env');
    }

    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    if ($mysqli->connect_error) {
        throw new RuntimeException('Connect failed: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');

    return $mysqli;
}

function runSqlFile(mysqli $mysqli, string $sqlFile, bool $dryRun): void
{
    if (! is_file($sqlFile)) {
        throw new RuntimeException("Missing SQL file: {$sqlFile}");
    }

    if ($dryRun) {
        out('Dry run: would execute ' . basename($sqlFile) . ' (' . filesize($sqlFile) . ' bytes)');

        return;
    }

    // Large Quran SQL: use statement-by-statement importer (multi_query often fails on live)
    if (basename($sqlFile) === 'live_quran_reference_data.sql') {
        $importScript = __DIR__ . '/import_quran_data.php';
        if (is_file($importScript)) {
            passthru(PHP_BINARY . ' ' . escapeshellarg($importScript), $code);
            if ($code !== 0) {
                throw new RuntimeException('Quran import failed (exit ' . $code . ')');
            }

            return;
        }
    }

    $sql = file_get_contents($sqlFile);
    if (! $mysqli->multi_query($sql)) {
        throw new RuntimeException('SQL error: ' . $mysqli->error);
    }
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    if ($mysqli->error) {
        throw new RuntimeException('SQL error: ' . $mysqli->error);
    }
}

function cmdMigrate(bool $dryRun): int
{
    if ($dryRun) {
        out('=== Step: migrations (dry run) ===');
        passthru(PHP_BINARY . ' spark migrate:status', $code);

        return $code;
    }

    out('=== Step 1/3: Applying pending migrations (structure only) ===');
    passthru(PHP_BINARY . ' spark migrate --all', $code);
    if ($code !== 0) {
        return $code;
    }
    out('Migrations applied.');
    passthru(PHP_BINARY . ' spark migrate:status', $code2);

    return $code2;
}

function cmdDropUnused(bool $dryRun): int
{
    $sqlFile = __DIR__ . '/live_drop_unused_objects.sql';
    out('=== Step: drop unused backup/orphan tables ===');

    if ($dryRun) {
        out('Dry run: would execute ' . basename($sqlFile));

        return 0;
    }

    $env = loadEnv($GLOBALS['root']);
    $mysqli = dbConnect($env);
    out('Database: ' . ($env['database.default.database'] ?? ''));
    runSqlFile($mysqli, $sqlFile, false);
    $mysqli->close();
    out('Unused tables/views removed.');

    return 0;
}

function cmdSeedQuran(bool $dryRun): int
{
    out('=== Step: Quran reference data ===');

    $sqlFile = __DIR__ . '/live_quran_reference_data.sql';
    if (is_file($sqlFile)) {
        out('Using exported SQL: live_quran_reference_data.sql');
        if ($dryRun) {
            out('Dry run: would import live_quran_reference_data.sql');

            return 0;
        }

        $env = loadEnv($GLOBALS['root']);
        $mysqli = dbConnect($env);
        runSqlFile($mysqli, $sqlFile, false);
        $mysqli->close();
        out('Quran reference data imported.');

        return 0;
    }

    out('SQL export not found — falling back to seeders (needs internet for ayah text).');
    if ($dryRun) {
        out('Would run: php spark db:seed QuranReferenceSeeder');
        out('Would run: php spark db:seed QuranAyahSeeder');

        return 0;
    }

    passthru(PHP_BINARY . ' spark db:seed QuranReferenceSeeder', $code);
    if ($code !== 0) {
        return $code;
    }
    passthru(PHP_BINARY . ' spark db:seed QuranAyahSeeder', $code2);

    return $code2;
}

if ($action === 'help' || $action === '-h') {
    out('Live database deploy (structure + Quran ref data only)');
    out('');
    out('  php tools/deploy_live_schema.php all           # migrate + drop-unused + seed-quran');
    out('  php tools/deploy_live_schema.php migrate       # new tables / columns only');
    out('  php tools/deploy_live_schema.php drop-unused   # remove backup tables');
    out('  php tools/deploy_live_schema.php seed-quran    # surah/juz/mushaf/ayah data');
    out('');
    out('Before running on live: backup the live database.');
    out('Does NOT run DefaultRolesSeeder or other app data seeders.');
    exit(0);
}

try {
    switch ($action) {
        case 'migrate':
            exit(cmdMigrate($dryRun));

        case 'drop-unused':
            exit(cmdDropUnused($dryRun));

        case 'seed-quran':
            exit(cmdSeedQuran($dryRun));

        case 'all':
            $code = cmdMigrate($dryRun);
            if ($code !== 0) {
                exit($code);
            }
            $code = cmdDropUnused($dryRun);
            if ($code !== 0) {
                exit($code);
            }
            exit(cmdSeedQuran($dryRun));

        default:
            fwrite(STDERR, "Unknown action: {$action}\n");
            exit(1);
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

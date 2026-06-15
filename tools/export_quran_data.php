<?php
/**
 * Export Quran reference DATA from local DB for live import.
 * Structure must already exist (run migrations first).
 *
 * Usage: php tools/export_quran_data.php
 */
$root = dirname(__DIR__);
$env = [];
foreach (file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') {
        continue;
    }
    if (str_contains($line, '=')) {
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \t\"'");
    }
}

$host = $env['database.default.hostname'] ?? 'localhost';
$user = $env['database.default.username'] ?? 'root';
$pass = $env['database.default.password'] ?? '';
$db   = $env['database.default.database'] ?? '';

$tables = [
    'quran_mushaf_layouts',
    'quran_surahs',
    'quran_juz_boundaries',
    'quran_mushaf_lines',
    'quran_ayahs',
];

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    fwrite(STDERR, 'Connect failed: ' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$outFile = $root . '/tools/live_quran_reference_data.sql';
$fh = fopen($outFile, 'w');
if (! $fh) {
    fwrite(STDERR, "Cannot write {$outFile}\n");
    exit(1);
}

fwrite($fh, "-- Quran reference data for live server\n");
fwrite($fh, "-- Run AFTER migrations. Safe to re-run: truncates Quran ref tables first.\n");
fwrite($fh, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n");

foreach ($tables as $table) {
    $check = $mysqli->query("SHOW TABLES LIKE '" . $mysqli->real_escape_string($table) . "'");
    if (! $check || $check->num_rows === 0) {
        echo "Skip (missing): {$table}\n";
        continue;
    }

    $countRes = $mysqli->query("SELECT COUNT(*) AS c FROM `{$table}`");
    $count = (int) ($countRes->fetch_assoc()['c'] ?? 0);
    echo "{$table}: {$count} rows\n";

    if ($count === 0) {
        fwrite($fh, "-- {$table}: empty on source, skipped\n\n");
        continue;
    }

    fwrite($fh, "DELETE FROM `{$table}`;\n");

    $res = $mysqli->query("SELECT * FROM `{$table}`");
    $cols = [];
    while ($field = $res->fetch_field()) {
        $cols[] = '`' . $field->name . '`';
    }
    $colList = implode(', ', $cols);

    $batch = [];
    $batchSize = 100;
    while ($row = $res->fetch_assoc()) {
        $vals = [];
        foreach ($row as $v) {
            if ($v === null) {
                $vals[] = 'NULL';
            } else {
                $vals[] = "'" . $mysqli->real_escape_string((string) $v) . "'";
            }
        }
        $batch[] = '(' . implode(', ', $vals) . ')';

        if (count($batch) >= $batchSize) {
            fwrite($fh, "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $batch) . ";\n");
            $batch = [];
        }
    }
    if ($batch !== []) {
        fwrite($fh, "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $batch) . ";\n");
    }
    fwrite($fh, "\n");
}

fwrite($fh, "SET FOREIGN_KEY_CHECKS = 1;\n");
fclose($fh);

echo "Exported: tools/live_quran_reference_data.sql\n";

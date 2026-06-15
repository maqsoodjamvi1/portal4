<?php

/**
 * One-way MySQL dump helper for hosts where mysqldump cannot authenticate but
 * PHP mysqlnd can. Credentials are read from environment variables.
 */

set_time_limit(0);
ini_set('memory_limit', '1024M');

$host = getenv('DO_DB_HOST') ?: '';
$port = (int) (getenv('DO_DB_PORT') ?: 25060);
$dbName = getenv('DO_DB_NAME') ?: '';
$user = getenv('DO_DB_USER') ?: '';
$pass = getenv('DO_DB_PASSWORD') ?: '';
$out = $argv[1] ?? '';
$startAt = getenv('DO_DB_START_AT') ?: '';

if ($host === '' || $dbName === '' || $user === '' || $pass === '' || $out === '') {
    fwrite(STDERR, "Usage: php tools/download_remote_mysql.php dump.sql\n");
    fwrite(STDERR, "Required env: DO_DB_HOST DO_DB_PORT DO_DB_NAME DO_DB_USER DO_DB_PASSWORD\n");
    exit(2);
}

$mysqli = mysqli_init();
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 20);

if (! $mysqli->real_connect($host, $user, $pass, $dbName, $port, null, MYSQLI_CLIENT_SSL)) {
    fwrite(STDERR, 'Remote connect failed: ' . mysqli_connect_error() . PHP_EOL);
    exit(1);
}

$mysqli->set_charset('utf8mb4');

$fh = fopen($out, 'wb');
if (! $fh) {
    fwrite(STDERR, "Cannot write dump: {$out}\n");
    exit(1);
}

function qid(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

function stripDefiner(string $sql): string
{
    return preg_replace('/DEFINER=`[^`]+`@`[^`]+`\s+/i', '', $sql) ?? $sql;
}

function writeLine($fh, string $line = ''): void
{
    fwrite($fh, $line . "\n");
}

function sqlValue(mysqli $db, mixed $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    return "'" . $db->real_escape_string((string) $value) . "'";
}

function tableNames(mysqli $db, string $dbName, string $type): array
{
    $sql = 'SELECT TABLE_NAME FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?
            ORDER BY TABLE_NAME';
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ss', $dbName, $type);
    $stmt->execute();
    $res = $stmt->get_result();
    $names = [];
    while ($row = $res->fetch_assoc()) {
        $names[] = $row['TABLE_NAME'];
    }
    $stmt->close();

    return $names;
}

writeLine($fh, '-- Remote dump downloaded via PHP mysqlnd');
writeLine($fh, '-- Database: ' . $dbName);
writeLine($fh, '-- Generated: ' . date('c'));
writeLine($fh, 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";');
writeLine($fh, 'SET time_zone = "+00:00";');
writeLine($fh, 'SET FOREIGN_KEY_CHECKS = 0;');
writeLine($fh, 'SET UNIQUE_CHECKS = 0;');
writeLine($fh);

$baseTables = tableNames($mysqli, $dbName, 'BASE TABLE');
$views = tableNames($mysqli, $dbName, 'VIEW');

if ($startAt !== '') {
    $baseTables = array_values(array_filter(
        $baseTables,
        static fn (string $table): bool => strcmp($table, $startAt) >= 0
    ));
}

foreach ($views as $view) {
    writeLine($fh, 'DROP VIEW IF EXISTS ' . qid($view) . ';');
}

foreach ($baseTables as $table) {
    fwrite(STDERR, "Starting {$table}\n");
    writeLine($fh, 'DROP TABLE IF EXISTS ' . qid($table) . ';');
    $createResult = $mysqli->query('SHOW CREATE TABLE ' . qid($table));
    if (! $createResult) {
        throw new RuntimeException('Create read failed for ' . $table . ': ' . $mysqli->error);
    }
    $create = $createResult->fetch_assoc();
    writeLine($fh, stripDefiner($create['Create Table']) . ';');
    writeLine($fh);

    $res = $mysqli->query('SELECT * FROM ' . qid($table), MYSQLI_USE_RESULT);
    if (! $res) {
        throw new RuntimeException('Data read failed for ' . $table . ': ' . $mysqli->error);
    }

    $fields = $res->fetch_fields();
    $columns = array_map(static fn ($field) => qid($field->name), $fields);
    $prefix = 'INSERT INTO ' . qid($table) . ' (' . implode(', ', $columns) . ') VALUES ';
    $rows = [];
    $count = 0;

    while ($row = $res->fetch_assoc()) {
        $values = [];
        foreach ($fields as $field) {
            $values[] = sqlValue($mysqli, $row[$field->name]);
        }
        $rows[] = '(' . implode(', ', $values) . ')';
        $count++;

        if (count($rows) >= 250) {
            writeLine($fh, $prefix . implode(",\n", $rows) . ';');
            $rows = [];
        }
    }

    if ($rows !== []) {
        writeLine($fh, $prefix . implode(",\n", $rows) . ';');
    }

    $res->free();
    writeLine($fh);
    fwrite(STDERR, "Dumped {$table}: {$count} rows\n");
}

foreach ($views as $view) {
    fwrite(STDERR, "Starting view {$view}\n");
    $createResult = $mysqli->query('SHOW CREATE VIEW ' . qid($view));
    if (! $createResult) {
        throw new RuntimeException('View read failed for ' . $view . ': ' . $mysqli->error);
    }
    $create = $createResult->fetch_assoc();
    writeLine($fh, 'DROP VIEW IF EXISTS ' . qid($view) . ';');
    writeLine($fh, stripDefiner($create['Create View']) . ';');
    writeLine($fh);
}

$triggers = $mysqli->query(
    "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = '" .
    $mysqli->real_escape_string($dbName) . "' ORDER BY TRIGGER_NAME"
);
while ($row = $triggers->fetch_assoc()) {
    $trigger = $row['TRIGGER_NAME'];
    $create = $mysqli->query('SHOW CREATE TRIGGER ' . qid($trigger))->fetch_assoc();
    writeLine($fh, 'DROP TRIGGER IF EXISTS ' . qid($trigger) . ';');
    writeLine($fh, 'DELIMITER ;;');
    writeLine($fh, stripDefiner($create['SQL Original Statement']) . ';;');
    writeLine($fh, 'DELIMITER ;');
    writeLine($fh);
}

$routines = $mysqli->query(
    "SELECT ROUTINE_NAME, ROUTINE_TYPE FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = '" .
    $mysqli->real_escape_string($dbName) . "' ORDER BY ROUTINE_TYPE, ROUTINE_NAME"
);
while ($row = $routines->fetch_assoc()) {
    $name = $row['ROUTINE_NAME'];
    $type = strtoupper($row['ROUTINE_TYPE']);
    $create = $mysqli->query('SHOW CREATE ' . $type . ' ' . qid($name))->fetch_assoc();
    $key = $type === 'PROCEDURE' ? 'Create Procedure' : 'Create Function';
    writeLine($fh, 'DROP ' . $type . ' IF EXISTS ' . qid($name) . ';');
    writeLine($fh, 'DELIMITER ;;');
    writeLine($fh, stripDefiner($create[$key]) . ';;');
    writeLine($fh, 'DELIMITER ;');
    writeLine($fh);
}

writeLine($fh, 'SET UNIQUE_CHECKS = 1;');
writeLine($fh, 'SET FOREIGN_KEY_CHECKS = 1;');
fclose($fh);

fwrite(STDERR, "Dump written: {$out}\n");

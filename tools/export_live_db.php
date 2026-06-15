<?php

declare(strict_types=1);

if ($argc < 7) {
    fwrite(STDERR, "Usage:\n");
    fwrite(STDERR, "  php export_live_db.php test  HOST PORT USER PASS DB\n");
    fwrite(STDERR, "  php export_live_db.php export HOST PORT USER PASS DB OUTPUT.sql\n");
    exit(1);
}

$mode = $argv[1];
$host = $argv[2];
$port = (int) $argv[3];
$user = $argv[4];
$pass = $argv[5];
$db   = $argv[6];

function connect(string $host, int $port, string $user, string $pass, string $db): mysqli
{
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 30);
    $mysqli->ssl_set(null, null, null, null, null);

    if (! @$mysqli->real_connect($host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL)) {
        throw new RuntimeException('Connect failed: ' . mysqli_connect_error());
    }

    return $mysqli;
}

function quote(mysqli $db, string $value): string
{
    return "'" . $db->real_escape_string($value) . "'";
}

try {
    $mysqli = connect($host, $port, $user, $pass, $db);

    if ($mode === 'test') {
        $result = $mysqli->query("SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = " . quote($mysqli, $db));
        $row = $result->fetch_assoc();
        echo "Connected. Tables: {$row['c']}" . PHP_EOL;
        exit(0);
    }

    if ($mode !== 'export') {
        throw new InvalidArgumentException('Unknown mode: ' . $mode);
    }

    $output = $argv[7] ?? '';
    if ($output === '') {
        throw new InvalidArgumentException('Output file path is required for export.');
    }

    $fp = fopen($output, 'wb');
    if ($fp === false) {
        throw new RuntimeException('Cannot open output file: ' . $output);
    }

    fwrite($fp, "-- Live export generated " . date('Y-m-d H:i:s') . PHP_EOL);
    fwrite($fp, "SET NAMES utf8mb4;" . PHP_EOL);
    fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;" . PHP_EOL);

    $tablesResult = $mysqli->query(
        "SELECT table_name FROM information_schema.tables
         WHERE table_schema = " . quote($mysqli, $db) . "
         ORDER BY table_name"
    );

    $tables = [];
    while ($row = $tablesResult->fetch_assoc()) {
        $tables[] = $row['table_name'];
    }

    echo 'Exporting ' . count($tables) . ' tables...' . PHP_EOL;

    foreach ($tables as $index => $table) {
        $safeTable = str_replace('`', '``', $table);
        echo '[' . ($index + 1) . '/' . count($tables) . "] $table" . PHP_EOL;

        $createResult = $mysqli->query("SHOW CREATE TABLE `$safeTable`");
        $createRow = $createResult->fetch_assoc();
        $createSql = $createRow['Create Table'] ?? $createRow['Create View'] ?? null;

        fwrite($fp, PHP_EOL . "DROP TABLE IF EXISTS `$safeTable`;" . PHP_EOL);
        if ($createSql !== null) {
            fwrite($fp, $createSql . ';' . PHP_EOL);
        }

        $countResult = $mysqli->query("SELECT COUNT(*) AS c FROM `$safeTable`");
        $countRow = $countResult->fetch_assoc();
        $totalRows = (int) $countRow['c'];

        if ($totalRows === 0) {
            continue;
        }

        $columnsResult = $mysqli->query("SHOW COLUMNS FROM `$safeTable`");
        $columns = [];
        while ($col = $columnsResult->fetch_assoc()) {
            $columns[] = '`' . str_replace('`', '``', $col['Field']) . '`';
        }
        $columnList = implode(', ', $columns);

        $batchSize = 500;
        for ($offset = 0; $offset < $totalRows; $offset += $batchSize) {
            $dataResult = $mysqli->query("SELECT * FROM `$safeTable` LIMIT $batchSize OFFSET $offset");
            if ($dataResult->num_rows === 0) {
                break;
            }

            $values = [];
            while ($row = $dataResult->fetch_assoc()) {
                $rowValues = [];
                foreach ($row as $value) {
                    $rowValues[] = $value === null ? 'NULL' : quote($mysqli, (string) $value);
                }
                $values[] = '(' . implode(', ', $rowValues) . ')';
            }

            fwrite($fp, "INSERT INTO `$safeTable` ($columnList) VALUES" . PHP_EOL);
            fwrite($fp, implode(',' . PHP_EOL, $values) . ';' . PHP_EOL);
        }
    }

    fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;" . PHP_EOL);
    fclose($fp);
    $mysqli->close();

    echo 'Done: ' . $output . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

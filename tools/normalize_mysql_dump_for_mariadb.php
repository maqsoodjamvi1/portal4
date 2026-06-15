<?php

set_time_limit(0);
ini_set('memory_limit', '512M');

$src = $argv[1] ?? '';
$dst = $argv[2] ?? '';

if ($src === '' || $dst === '') {
    fwrite(STDERR, "Usage: php tools/normalize_mysql_dump_for_mariadb.php source.sql target.sql\n");
    exit(2);
}

$in = fopen($src, 'rb');
if (! $in) {
    fwrite(STDERR, "Cannot read {$src}\n");
    exit(1);
}

$out = fopen($dst, 'wb');
if (! $out) {
    fwrite(STDERR, "Cannot write {$dst}\n");
    exit(1);
}

$replace = [
    'utf8mb4_0900_ai_ci' => 'utf8mb4_general_ci',
    'utf8mb4_0900_bin'   => 'utf8mb4_bin',
];

$pending = null;

while (($line = fgets($in)) !== false) {
    $line = strtr($line, $replace);

    if (preg_match('/^\s*CONSTRAINT\s+`[^`]+`\s+CHECK\s+/i', $line)) {
        continue;
    }

    if ($pending !== null && preg_match('/^\)\s+ENGINE=/i', $line)) {
        $pending = preg_replace('/,\s*$/', '', rtrim($pending, "\r\n")) . PHP_EOL;
    }

    if ($pending !== null) {
        fwrite($out, $pending);
    }

    $pending = $line;
}

if ($pending !== null) {
    fwrite($out, $pending);
}

fclose($in);
fclose($out);

fwrite(STDERR, "Normalized dump written: {$dst}\n");

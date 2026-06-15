<?php
/**
 * Import Quran reference data.
 * Uses CodeIgniter DotEnv (same parsing as spark migrate).
 *
 * Usage from project root:
 *   php tools/import_quran_data.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$sqlFile = __DIR__ . '/live_quran_reference_data.sql';

if (! is_file($sqlFile)) {
    fwrite(STDERR, "Missing: {$sqlFile}\n");
    exit(1);
}

require $root . '/vendor/autoload.php';
(new CodeIgniter\Config\DotEnv($root))->load();

$env = static function (string $key, $default = '') {
    $v = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($v === false || $v === null || $v === '') {
        return $default;
    }
    $v = trim((string) $v, " \t\"'");
    // Strip inline comments accidentally pasted into .env values
    if (($hash = strpos($v, '#')) !== false) {
        $v = trim(substr($v, 0, $hash));
    }

    return $v;
};

$host = $env('database.default.hostname');
$port = (int) $env('database.default.port', 3306);
$db   = $env('database.default.database');
$user = $env('database.default.username');
$pass = $env('database.default.password');

// Fallback: read app/Config/Database.php if .env hostname is still bad
if ($host === '' || str_contains($host, ' ') || str_contains($host, '#')) {
    $dbPhp = $root . '/app/Config/Database.php';
    if (is_file($dbPhp)) {
        $src = file_get_contents($dbPhp);
        if (preg_match("/'hostname'\s*=>\s*'([^']+)'/", $src, $m)) {
            $host = $m[1];
        }
        if (preg_match("/'port'\s*=>\s*(\d+)/", $src, $m)) {
            $port = (int) $m[1];
        }
        if (preg_match("/'database'\s*=>\s*'([^']+)'/", $src, $m)) {
            $db = $m[1];
        }
        if (preg_match("/'username'\s*=>\s*'([^']+)'/", $src, $m)) {
            $user = $m[1];
        }
        if (preg_match("/'password'\s*=>\s*'([^']+)'/", $src, $m)) {
            $pass = $m[1];
        }
        fwrite(STDERR, "Note: using credentials from app/Config/Database.php (.env hostname had inline comment)\n");
    }
}

if ($host === '' || $db === '') {
    fwrite(STDERR, "Database settings missing in .env\n");
    exit(1);
}

$mysqli = @new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_error) {
    fwrite(STDERR, "Connect failed: {$mysqli->connect_error}\n");
    fwrite(STDERR, "Host: {$host}:{$port}  DB: {$db}\n");
    fwrite(STDERR, "Tip: remove inline comments from .env values (e.g. hostname ... # note)\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

echo "Database: {$db}\n";
echo "Importing: " . basename($sqlFile) . ' (' . round(filesize($sqlFile) / 1024 / 1024, 2) . " MB)\n";

$sql = file_get_contents($sqlFile);
$statements = [];
$buffer = '';
foreach (explode("\n", (string) $sql) as $line) {
    $trim = trim($line);
    if ($trim === '' || str_starts_with($trim, '--')) {
        continue;
    }
    $buffer .= $line . "\n";
    if (str_ends_with(trim($line), ';')) {
        $statements[] = trim($buffer);
        $buffer = '';
    }
}

$ok = 0;
$fail = 0;
foreach ($statements as $i => $stmt) {
    if ($stmt === '') {
        continue;
    }
    if (! $mysqli->query($stmt)) {
        $fail++;
        fwrite(STDERR, "FAIL #{$i}: {$mysqli->error}\n");
        if ($fail >= 5) {
            break;
        }
        continue;
    }
    $ok++;
    if ($ok % 50 === 0) {
        echo "  ... {$ok} statements OK\n";
    }
}

echo "Statements OK: {$ok}, failed: {$fail}\n\nRow counts:\n";
foreach (['quran_mushaf_layouts', 'quran_surahs', 'quran_juz_boundaries', 'quran_mushaf_lines', 'quran_ayahs'] as $t) {
    $r = $mysqli->query("SELECT COUNT(*) AS c FROM `{$t}`");
    echo "  {$t}: " . (int) ($r?->fetch_assoc()['c'] ?? 0) . "\n";
}

$mysqli->close();
exit($fail > 0 ? 1 : 0);

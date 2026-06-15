<?php

/**
 * One-time helper: split app/Config/Routes.php into domain includes.
 * Run: php tools/split_routes.php
 */

$root     = dirname(__DIR__);
$src      = $root . '/app/Config/Routes.php';
$outDir   = $root . '/app/Config/Routes';
$backup   = $root . '/app/Config/Routes.php.bak-split';

if (! is_file($src)) {
    fwrite(STDERR, "Missing {$src}\n");
    exit(1);
}

$lines = file($src, FILE_IGNORE_NEW_LINES);
if ($lines === false) {
    exit(1);
}

// 1-based inclusive line ranges => filename (body only; header added per file)
$chunks = [
    'PublicRoutes.php'        => [22, 33],
    'AdminPriority.php'       => [35, 112],
    'AdminHealthSalary.php'   => [113, 198],
    'AdminUsersRoles.php'     => [199, 463],
    'AdminAcademicSetup.php'  => [464, 596],
    'AdminClasses.php'        => [597, 661],
    'AdminStudents.php'       => [662, 1051],
    'AdminFees.php'           => [1052, 1199],
    'AdminExams.php'          => [1200, 1577],
    'AdminAttendance.php'     => [1578, 1909],
    'AdminCampusFinance.php'  => [1910, 2371],
    'AdminQuizzes.php'        => [2373, 2408],
    'AdminSports.php'         => [2411, 2596],
    'AdminMisc.php'           => [2599, 2728],
];

$headerTpl = <<<'HDR'
<?php

/**
 * %s
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

HDR;

if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$claimed = [];
foreach ($chunks as $file => [$start, $end]) {
    for ($i = $start; $i <= $end; $i++) {
        $claimed[$i] = true;
    }
    $slice = array_slice($lines, $start - 1, $end - $start + 1);
    $body  = implode("\n", $slice) . "\n";
    $label = str_replace(['.php', '_'], ['', ' '], $file);
    $content = sprintf($headerTpl, $label) . $body;
    file_put_contents($outDir . '/' . $file, $content);
    echo "Wrote {$file} (" . count($slice) . " lines)\n";
}

// Bootstrap: lines 1-21 + requires
$bootstrap = array_slice($lines, 0, 21);
$bootstrap[] = '';
$bootstrap[] = '// Domain route files (split from monolithic Routes.php — load order matters)';
$loadOrder = array_keys($chunks);
foreach ($loadOrder as $file) {
    $bootstrap[] = "require __DIR__ . '/Routes/{$file}';";
}
$bootstrap[] = '';
$bootstrap[] = "require __DIR__ . '/Routes/AdminHifz.php';";
$bootstrap[] = "require __DIR__ . '/Routes/AdminReports.php';";
$bootstrap[] = '';
$bootstrap[] = '// Legacy dispatcher + admin/(:segment) fallback — must load after explicit admin routes';
$bootstrap[] = "require __DIR__ . '/Routes/AdminLegacy.php';";
$bootstrap[] = '';
$bootstrap[] = "\$routes->get('media/qb/(:any)', 'Media::qbQuestionImage/\$1');";
$bootstrap[] = '';
$bootstrap[] = "require __DIR__ . '/Routes/StudentPortal.php';";

// Check unclaimed lines in original range 22-2728
$missed = [];
for ($i = 22; $i <= 2728; $i++) {
    if (empty($claimed[$i])) {
        $missed[] = $i;
    }
}
if ($missed !== []) {
    echo 'WARNING: unclaimed lines: ' . implode(', ', array_slice($missed, 0, 20));
    if (count($missed) > 20) {
        echo ' ... (' . count($missed) . ' total)';
    }
    echo "\n";
}

copy($src, $backup);
file_put_contents($src, implode("\n", $bootstrap) . "\n");
echo "Backed up to Routes.php.bak-split and wrote new slim Routes.php\n";

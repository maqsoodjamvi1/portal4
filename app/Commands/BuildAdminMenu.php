<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Rebuild app/Libraries/AdminMenuSections.inc.php from app/Libraries/Menu/*.php partials.
 *
 * Usage: php spark menu:build
 */
class BuildAdminMenu extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'menu:build';
    protected $description = 'Rebuild AdminMenuSections.inc.php from Menu partials';
    protected $usage       = 'menu:build';

    public function run(array $params): void
    {
        $menuDir = APPPATH . 'Libraries/Menu';
        $outFile = APPPATH . 'Libraries/AdminMenuSections.inc.php';

        $partials = [
            '01_dashboard_profiles_sessions.php',
            '02_faculty_exams.php',
            '03_academics_finance.php',
            '04_modules_billing.php',
        ];

        $lines = [
            '<?php',
            '',
            '/**',
            ' * AUTO-GENERATED — do not edit by hand.',
            ' * Edit app/Libraries/Menu/*.php then run: php spark menu:build',
            ' */',
            '$sections = [];',
            '',
        ];

        foreach ($partials as $file) {
            $path = $menuDir . DIRECTORY_SEPARATOR . $file;
            if (! is_file($path)) {
                CLI::error('Missing partial: ' . $path);

                return;
            }

            $chunk = file_get_contents($path);
            $chunk = $this->normalizePartial($chunk, $file === '01_dashboard_profiles_sessions.php');
            $lines[] = rtrim($chunk);
            $lines[] = '';
        }

        $qualityPath = $menuDir . DIRECTORY_SEPARATOR . '_quality_pass.php';
        if (! is_file($qualityPath)) {
            CLI::error('Missing _quality_pass.php');

            return;
        }

        $quality = file_get_contents($qualityPath);
        $quality = preg_replace('/^<\?php\s*/', '', ltrim($quality));
        $lines[] = rtrim($quality);
        $lines[] = '';

        $output = implode("\n", $lines);
        $output = $this->fixMojibake($output);

        if (! file_put_contents($outFile, $output)) {
            CLI::error('Failed to write ' . $outFile);

            return;
        }

        $php = CLI::getOption('php') ?? (defined('PHP_BINARY') ? PHP_BINARY : 'php');
        $lint  = escapeshellarg($php) . ' -l ' . escapeshellarg($outFile);
        exec($lint, $lintOut, $code);

        foreach ($lintOut as $line) {
            CLI::write($line);
        }

        if ($code !== 0) {
            CLI::error('Syntax check failed.');

            return;
        }

        CLI::write('Built ' . $outFile, 'green');
    }

    private function normalizePartial(string $chunk, bool $isFirst): string
    {
        $chunk = preg_replace('/^\xEF\xBB\xBF/', '', $chunk);
        $chunk = preg_replace('/^<\?php\s*/', '', ltrim($chunk));

        if ($isFirst) {
            $chunk = preg_replace('/^\$sections\s*=\s*\[\]\s*;\s*/m', '', $chunk);
        }

        $chunk = preg_replace('/\s*include\s+__DIR__\s*\.\s*[\'"]\/Menu\/_quality_pass\.php[\'"]\s*;\s*/', "\n", $chunk);

        return trim($chunk) . "\n";
    }

    private function fixMojibake(string $text): string
    {
        $map = [
            'â€"' => '—',
            'â€”' => '—',
        ];

        return str_replace(array_keys($map), array_values($map), $text);
    }
}

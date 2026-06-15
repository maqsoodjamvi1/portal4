# Rebuild AdminMenuSections.inc.php (requires PHP on PATH or XAMPP)
$php = $env:PHP_BIN
if (-not $php) {
    $candidates = @(
        'C:\xhamp\php\php.exe',
        'C:\xampp\php\php.exe'
    )
    foreach ($c in $candidates) {
        if (Test-Path $c) { $php = $c; break }
    }
}
if (-not $php) {
    Write-Error 'PHP not found. Set PHP_BIN or install XAMPP.'
    exit 1
}
Set-Location (Split-Path -Parent $PSScriptRoot)
& $php spark menu:build
exit $LASTEXITCODE

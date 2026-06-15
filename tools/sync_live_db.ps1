param(
    [Parameter(Mandatory = $true)]
    [string]$Password,

    [string]$HostName = $env:PORTAL4_LIVE_DB_HOST,
    [int]$Port = $(if ($env:PORTAL4_LIVE_DB_PORT) { [int]$env:PORTAL4_LIVE_DB_PORT } else { 25060 }),
    [string]$User = $env:PORTAL4_LIVE_DB_USER,
    [string]$Database = $env:PORTAL4_LIVE_DB_NAME,
    [string]$Output = "C:\xhamp\htdocs\live_dump.sql",
    [string]$PhpExe = "C:\xhamp\php\php.exe",
    [string]$Script = "$PSScriptRoot\export_live_db.php"
)

if ([string]::IsNullOrWhiteSpace($HostName) -or
    [string]::IsNullOrWhiteSpace($User) -or
    [string]::IsNullOrWhiteSpace($Database)) {
    Write-Error "Provide -HostName, -User, and -Database, or set PORTAL4_LIVE_DB_HOST, PORTAL4_LIVE_DB_USER, and PORTAL4_LIVE_DB_NAME."
    exit 1
}

Write-Host "Testing live connection..."
& $PhpExe $Script test $HostName $Port $User $Password $Database
if ($LASTEXITCODE -ne 0) {
    Write-Error "Connection failed. Copy the current password from DigitalOcean Overview (click 'show') and run again."
    exit 1
}

Write-Host "Exporting database to $Output ..."
& $PhpExe $Script export $HostName $Port $User $Password $Database $Output
if ($LASTEXITCODE -ne 0) {
    Write-Error "Export failed."
    exit 1
}

Write-Host "Export complete."
Write-Host "Import with:"
Write-Host "  powershell -ExecutionPolicy Bypass -File `"$PSScriptRoot\import_dump.ps1`" -DumpFile `"$Output`""

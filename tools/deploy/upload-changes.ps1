# Upload only modified local files to the server via WinSCP (no git required).
#
# Setup:
#   1. Copy deploy-config.example.ps1 to deploy-config.local.ps1
#   2. Fill in Host, User, RemotePath, etc.
#   3. Run: powershell -ExecutionPolicy Bypass -File tools\deploy\upload-changes.ps1
#
# Optional:
#   -DryRun   Show what would upload without transferring files
#   -Mirror   Also delete remote files that no longer exist locally (use with care)

param(
    [switch]$DryRun,
    [switch]$Mirror
)

$ErrorActionPreference = 'Stop'
$scriptDir = $PSScriptRoot
$configFile = Join-Path $scriptDir 'deploy-config.local.ps1'

if (-not (Test-Path $configFile)) {
    Write-Error @"
Missing deploy-config.local.ps1

Copy deploy-config.example.ps1 to deploy-config.local.ps1 and edit your server settings.
"@
    exit 1
}

. $configFile

$required = @('LocalPath', 'RemotePath', 'Host', 'User', 'WinScpPath')
foreach ($key in $required) {
    if ([string]::IsNullOrWhiteSpace($DeployConfig[$key])) {
        Write-Error "deploy-config.local.ps1: '$key' is required."
        exit 1
    }
}

if (-not (Test-Path $DeployConfig.LocalPath)) {
    Write-Error "LocalPath not found: $($DeployConfig.LocalPath)"
    exit 1
}

if (-not (Test-Path $DeployConfig.WinScpPath)) {
    Write-Error "WinSCP.com not found: $($DeployConfig.WinScpPath). Install WinSCP or update WinScpPath."
    exit 1
}

$password = $DeployConfig.Password
if ([string]::IsNullOrWhiteSpace($password) -and [string]::IsNullOrWhiteSpace($DeployConfig.PrivateKeyPath)) {
    $secure = Read-Host "SFTP password for $($DeployConfig.User)@$($DeployConfig.Host)" -AsSecureString
    $password = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
        [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
    )
}

$criteria = if ($DeployConfig.SyncCriteria) { $DeployConfig.SyncCriteria } else { 'time' }
$port = if ($DeployConfig.Port) { $DeployConfig.Port } else { 22 }

# Exclude server-managed / generated paths (same idea as .gitignore)
$fileMask = '|vendor/;writable/;node_modules/;.git/;.env;*.log;*.zip;public/uploads/;public/system-logo/;public/assets/uploads/'

$openLine = "open sftp://$($DeployConfig.User)@$($DeployConfig.Host):$port/"

if ($DeployConfig.PrivateKeyPath) {
    $keyPath = $DeployConfig.PrivateKeyPath -replace '\\', '/'
    $openLine += " -privatekey=`"$keyPath`""
} else {
    $escapedPassword = $password -replace '%', '%%'
    $openLine = "open sftp://$($DeployConfig.User):$escapedPassword@$($DeployConfig.Host):$port/"
}

if ($DeployConfig.HostKey) {
    $openLine += " -hostkey=`"$($DeployConfig.HostKey)`""
} else {
    $openLine += ' -hostkey=*'
}

$localPath = $DeployConfig.LocalPath -replace '\\', '/'
$remotePath = $DeployConfig.RemotePath.TrimEnd('/')

$syncFlags = @(
    'remote',
    "`"$localPath`"",
    "`"$remotePath`"",
    "-criteria=$criteria",
    '-transfer=binary',
    "-filemask=`"$fileMask`""
)

if ($Mirror) {
    $syncFlags += '-mirror'
}

if ($DryRun) {
    $syncFlags += '-preview'
}

$winScpScript = @(
    'option batch abort'
    'option confirm off'
    $openLine
    ('synchronize ' + ($syncFlags -join ' '))
    'exit'
) -join "`r`n"

$tempScript = Join-Path $env:TEMP ("winscp-upload-{0}.txt" -f ([guid]::NewGuid().ToString('N')))
$logFile = Join-Path $scriptDir ("upload-{0:yyyyMMdd-HHmmss}.log" -f (Get-Date))

try {
    Set-Content -Path $tempScript -Value $winScpScript -Encoding ASCII

    Write-Host ''
    Write-Host 'WinSCP sync starting...'
    Write-Host "  Local : $($DeployConfig.LocalPath)"
    Write-Host "  Remote: $($DeployConfig.RemotePath)"
    Write-Host "  Mode  : $(if ($DryRun) { 'preview (dry run)' } elseif ($Mirror) { 'mirror (upload + delete)' } else { 'upload changed only' })"
    Write-Host ''

    & $DeployConfig.WinScpPath "/script=$tempScript" "/log=$logFile"

    if ($LASTEXITCODE -ne 0) {
        Write-Error "Upload failed. See log: $logFile"
        exit $LASTEXITCODE
    }

    Write-Host "Done. Log: $logFile"
} finally {
    if (Test-Path $tempScript) {
        Remove-Item $tempScript -Force
    }
}

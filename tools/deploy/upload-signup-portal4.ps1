# Upload ONLY signup page files to portal4 (forces overwrite).
#
# Setup: copy deploy-config.example.ps1 → deploy-config.local.ps1
#        Set RemotePath = '/var/www/portal4/html'
#
# Run: powershell -ExecutionPolicy Bypass -File tools\deploy\upload-signup-portal4.ps1

param([switch]$DryRun)

$ErrorActionPreference = 'Stop'
$scriptDir = $PSScriptRoot
$configFile = Join-Path $scriptDir 'deploy-config.local.ps1'

if (-not (Test-Path $configFile)) {
    Write-Error "Missing deploy-config.local.ps1 — copy deploy-config.example.ps1 and set RemotePath to /var/www/portal4/html"
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

$localRoot = $DeployConfig.LocalPath.TrimEnd('\', '/')
$remoteRoot = $DeployConfig.RemotePath.TrimEnd('/')
$port = if ($DeployConfig.Port) { $DeployConfig.Port } else { 22 }

$files = @(
    @{ Local = 'app\Views\trial_signup\form.php';       Remote = "$remoteRoot/app/Views/trial_signup/" },
    @{ Local = 'app\Controllers\TrialSignup.php';       Remote = "$remoteRoot/app/Controllers/" }
)

$password = $DeployConfig.Password
if ([string]::IsNullOrWhiteSpace($password) -and [string]::IsNullOrWhiteSpace($DeployConfig.PrivateKeyPath)) {
    $secure = Read-Host "SFTP password for $($DeployConfig.User)@$($DeployConfig.Host)" -AsSecureString
    $password = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
        [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
    )
}

$openLine = "open sftp://$($DeployConfig.User)@$($DeployConfig.Host):$port/"
if ($DeployConfig.PrivateKeyPath) {
    $keyPath = $DeployConfig.PrivateKeyPath -replace '\\', '/'
    $openLine += " -privatekey=`"$keyPath`""
} else {
    $escapedPassword = $password -replace '%', '%%'
    $openLine = "open sftp://$($DeployConfig.User):$escapedPassword@$($DeployConfig.Host):$port/"
}
$openLine += if ($DeployConfig.HostKey) { " -hostkey=`"$($DeployConfig.HostKey)`"" } else { ' -hostkey=*' }

$lines = @('option batch abort', 'option confirm off', $openLine)

foreach ($f in $files) {
    $localFile = Join-Path $localRoot $f.Local
    if (-not (Test-Path $localFile)) {
        Write-Error "Local file missing: $localFile"
        exit 1
    }
    $localFileEsc = ($localFile -replace '\\', '/')
    if ($DryRun) {
        $lines += "echo PUT $localFileEsc -> $($f.Remote)"
    } else {
        $lines += "put -force `"$localFileEsc`" `"$($f.Remote)`""
    }
}

$itiLocal = Join-Path $localRoot 'public\resource\intl-tel-input'
$itiRemote = "$remoteRoot/public/resource/intl-tel-input"
if (-not (Test-Path $itiLocal)) {
    Write-Error "Missing intl-tel-input folder: $itiLocal"
    exit 1
}
$itiLocalEsc = ($itiLocal -replace '\\', '/')
if ($DryRun) {
    $lines += "echo SYNC $itiLocalEsc -> $itiRemote"
} else {
    $lines += "synchronize remote `"$itiLocalEsc`" `"$itiRemote`" -filemask=`"|.git/;Thumbs.db`""
}

$lines += 'exit'
$winScpScript = $lines -join "`r`n"

$tempScript = Join-Path $env:TEMP ("winscp-signup-{0}.txt" -f ([guid]::NewGuid().ToString('N')))
Set-Content -Path $tempScript -Value $winScpScript -Encoding ASCII

Write-Host ''
Write-Host 'Uploading signup files to portal4...'
Write-Host "  Remote root: $remoteRoot"
Write-Host ''

if ($DryRun) {
    Write-Host $winScpScript
    Remove-Item $tempScript -Force
    exit 0
}

& $DeployConfig.WinScpPath "/script=$tempScript"
$code = $LASTEXITCODE
Remove-Item $tempScript -Force

if ($code -ne 0) {
    Write-Error 'Upload failed.'
    exit $code
}

Write-Host 'Done. Verify: https://portal4.timesoftsol.com/signup — look for "Signup form v3" in footer.'

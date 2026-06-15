param(
    [Parameter(Mandatory = $true)]
    [string]$DumpFile,

    [string]$Database = "portal4",
    [string]$MysqlBin = "C:\xhamp\mysql\bin",
    [string]$User = "root",
    [string]$Password = ""
)

$mysql = Join-Path $MysqlBin "mysql.exe"

if (-not (Test-Path $mysql)) {
    Write-Error "mysql.exe not found at $mysql"
    exit 1
}

if (-not (Test-Path $DumpFile)) {
    Write-Error "Dump file not found: $DumpFile"
    exit 1
}

Write-Host "Recreating local database '$Database'..."
if ($Password -eq "") {
    & $mysql -u $User -e "DROP DATABASE IF EXISTS ``$Database``; CREATE DATABASE ``$Database`` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
} else {
    & $mysql -u $User -p$Password -e "DROP DATABASE IF EXISTS ``$Database``; CREATE DATABASE ``$Database`` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
}

if ($LASTEXITCODE -ne 0) {
    Write-Error "Failed to recreate database."
    exit 1
}

Write-Host "Importing dump. This can take a while for large databases..."
if ($Password -eq "") {
    Get-Content $DumpFile | & $mysql -u $User $Database
} else {
    Get-Content $DumpFile | & $mysql -u $User -p$Password $Database
}

if ($LASTEXITCODE -ne 0) {
    Write-Error "Import failed."
    exit 1
}

Write-Host "Import completed successfully into '$Database'."

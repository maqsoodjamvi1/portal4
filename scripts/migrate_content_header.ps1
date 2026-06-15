$root = Join-Path $PSScriptRoot '..\app\Views'
$skip = @('components\page_header.php', 'layouts\admin_template.php')
$migrated = @()

Get-ChildItem -Path $root -Recurse -Filter '*.php' | ForEach-Object {
    $rel = $_.FullName.Substring($root.Length + 1)
    if ($skip -contains ($rel -replace '/', '\')) { return }
    $text = [IO.File]::ReadAllText($_.FullName)
    if ($text -notmatch '<section class="content-header">') { return }

    $changed = $false
    $newText = [regex]::Replace($text, '(?is)<section\s+class="content-header">(.*?)</section>', {
        param($m)
        $block = $m.Value
        if ($block -match 'sms-page-header') { return $block }
        $inner = $m.Groups[1].Value
        if ($inner -notmatch '(?is)<h1[^>]*>(.*?)</h1>') { return $block }
        $h1Inner = $Matches[1]
        $icon = $null
        if ($h1Inner -match '(?is)<i\s+class="([^"]+)"') { $icon = $Matches[1] }
        $title = ($h1Inner -replace '(?s)<[^>]+>', ' ' -replace '\s+', ' ').Trim()
        if (-not $title) { return $block }
        if ($inner -notmatch 'breadcrumb' -and $inner -match 'btn') { return $block }
        $active = $title
        if ($inner -match '(?is)<li\s+class="breadcrumb-item\s+active"[^>]*>(.*?)</li>') {
            $active = ($Matches[1] -replace '(?s)<[^>]+>', ' ' -replace '\s+', ' ').Trim()
        }
        $dashUrl = "<?= base_url('admin/dashboard') ?>"
        if ($inner -match '(?is)<li\s+class="breadcrumb-item"[^>]*>\s*<a[^>]+href="([^"]+)"') {
            $u = $Matches[1]
            if ($u -match 'base_url') { $dashUrl = $u } elseif ($u -match '^admin/') { $dashUrl = "<?= base_url('$u') ?>" }
        }
        $script:changed = $true
        $lines = @("<?= view('components/page_header', [", "    'title' => '$($title -replace "'","\'")',")
        if ($icon) { $lines += "    'icon' => '$icon'," }
        $lines += @(
            "    'breadcrumbs' => [",
            "        ['label' => 'Dashboard', 'url' => $dashUrl],",
            "        ['label' => '$($active -replace "'","\'")', 'active' => true],",
            "    ],",
            "]) ?>"
        )
        return ($lines -join "`n") + "`n"
    })

    if (-not $changed) { return }
    if ($newText -match 'DataTable\s*\(|id="[^"]*datatable' -and $newText.Substring(0, [Math]::Min(800, $newText.Length)) -notmatch 'uiNeedsDataTables') {
        if ($newText.TrimStart().StartsWith('<?=')) {
            $newText = "<?php `$uiNeedsDataTables = true; ?>`n" + $newText
        } elseif ($newText.TrimStart().StartsWith('<?php')) {
            $newText = "<?php `$uiNeedsDataTables = true; ?>`n" + $newText
        }
    }
    [IO.File]::WriteAllText($_.FullName, $newText)
    $migrated += $rel
}

"Migrated $($migrated.Count) files"
$migrated | ForEach-Object { "  $_" }

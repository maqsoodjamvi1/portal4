param(
    [switch] $IncludeIgnored,
    [switch] $ShowLocalOnly,
    [switch] $Buckets,
    [string] $Bucket,
    [switch] $PathsOnly,
    [switch] $GitAddCommand
)

function Get-ReviewBucketRules {
    @(
        [pscustomobject]@{
            Name = '01 hygiene/tooling'
            Patterns = @(
                '^\.gitattributes$',
                '^\.gitignore$',
                '^docs/(git-worktree-hygiene|worktree-audit)\.md$',
                '^docs/$',
                '^tools/git-status-summary\.ps1$'
            )
        },
        [pscustomobject]@{
            Name = '02 shared UI compatibility'
            Patterns = @(
                '^public/assets/css/school-forms\.css$',
                '^public/assets/js/bootstrap5-compat\.js$',
                '^app/Views/layouts/header\.php$'
            )
        },
        [pscustomobject]@{
            Name = '03 routing/admin shell'
            Patterns = @(
                '^app/Config/Routes\.php$',
                '^app/Config/Routes/',
                '^app/Views/layouts/partials/',
                '^app/Libraries/AdminMenuBuilder\.php$',
                '^app/Libraries/Menu/'
            )
        },
        [pscustomobject]@{
            Name = '04 admin modules'
            Patterns = @(
                '^app/Controllers/Admin/',
                '^app/Views/admin/',
                '^app/Models/Admin/'
            )
        },
        [pscustomobject]@{
            Name = '05 portal/frontend modules'
            Patterns = @(
                '^app/Controllers/Frontend/',
                '^app/Controllers/Parent/',
                '^app/Views/frontend/',
                '^app/Views/parent/',
                '^app/Views/student/'
            )
        },
        [pscustomobject]@{
            Name = '06 new domains/migrations'
            Patterns = @(
                'BoardPrep',
                'board_prep',
                'Hifz',
                'QuestionPaper',
                'question_paper',
                'Crossword',
                'crossword',
                'WordSearch',
                'word_search',
                'MathWorksheet',
                'math_worksheet',
                'RoleMenu',
                'role',
                'Salary',
                'CampusFinance',
                '^app/Database/Migrations/',
                '^app/Database/Seeds/'
            )
        },
        [pscustomobject]@{
            Name = '07 asset duplication review'
            Patterns = @(
                '^app/assets/',
                '^public/assets/',
                '^public/leaving_certificate/assets/'
            )
        },
        [pscustomobject]@{
            Name = '08 core/shared app infrastructure'
            Patterns = @(
                '^app/Commands/',
                '^app/Common/',
                '^app/Common\.php$',
                '^app/Config/',
                '^app/\.htaccess$',
                '^app/Controllers/AcademicSetup\.php$',
                '^app/Controllers/AdminDispatcher\.php$',
                '^app/Controllers/AdvanceFee\.php$',
                '^app/Controllers/Api/',
                '^app/Controllers/BaseController\.php$',
                '^app/Controllers/Home\.php$',
                '^app/Controllers/Language',
                '^app/Controllers/Media\.php$',
                '^app/Controllers/PublicBaseController\.php$',
                '^app/Controllers/PublicQuiz\.php$',
                '^app/Controllers/QrDebug\.php$',
                '^app/Controllers/Settings\.php$',
                '^app/Controllers/TrialSignup\.php$',
                '^app/Controllers/UploadsProxy\.php$',
                '^app/Database/Common/',
                '^app/Filters/',
                '^app/Helpers/',
                '^app/Language/',
                '^app/Libraries/',
                '^app/Models/',
                '^app/Services/',
                '^app/Views/components/',
                '^app/Views/emails/',
                '^app/Views/errors/',
                '^app/Views/layouts/',
                '^app/Views/timetable/',
                '^app/Views/trial_signup/',
                '^app/Views/welcome_message\.php$',
                '^composer\.(json|lock)$',
                '^env\.email\.example$',
                '^phpunit',
                '^tests/'
            )
        },
        [pscustomobject]@{
            Name = '09 legacy/public resources'
            Patterns = @(
                '^public/resource/',
                '^public/\.htaccess$',
                '^app/index\.html$',
                '^timesoftsol_page\.html$'
            )
        },
        [pscustomobject]@{
            Name = '10 deploy/scripts/manual ops'
            Patterns = @(
                '^scripts/',
                '^tools/'
            )
        },
        [pscustomobject]@{
            Name = '11 project documentation'
            Patterns = @(
                '^docs/'
            )
        }
    )
}

function Get-ReviewBucketName {
    param(
        [string] $Path,
        [array] $Rules
    )

    $normalizedPath = $Path -replace '\\', '/'
    foreach ($rule in $Rules) {
        foreach ($pattern in $rule.Patterns) {
            if ($normalizedPath -match $pattern) {
                return $rule.Name
            }
        }
    }

    '99 uncategorized'
}

function Get-StatusItems {
    param([string[]] $GitStatusLines)

    foreach ($line in $GitStatusLines) {
        if ($line.Length -lt 4) {
            continue
        }

        $code = $line.Substring(0, 2)
        $path = $line.Substring(3).Trim('"')
        if ($path -match ' -> ') {
            $path = ($path -split ' -> ', 2)[1].Trim('"')
        }

        $top = ($path -split '[\\/]', 2)[0]
        if ([string]::IsNullOrWhiteSpace($top)) {
            $top = '(root)'
        }

        [pscustomobject]@{
            Code = $code
            Top = $top
            Path = $path
        }
    }
}

function Format-GitPathArgument {
    param([string] $Path)

    "'" + ($Path -replace "'", "''") + "'"
}

$argsList = @('status', '--porcelain=v1', '--untracked-files=all')
if ($IncludeIgnored) {
    $argsList += '--ignored'
}

$status = & git @argsList
if (-not $status) {
    Write-Host 'Working tree is clean.'
    exit 0
}

$items = @(Get-StatusItems -GitStatusLines $status)
$bucketRules = @(Get-ReviewBucketRules)
$bucketed = @(
    foreach ($item in $items) {
        [pscustomobject]@{
            Bucket = Get-ReviewBucketName -Path $item.Path -Rules $bucketRules
            Code = $item.Code
            Path = $item.Path
        }
    }
)
$compactBucketOutput = $Bucket -and ($PathsOnly -or $GitAddCommand)

if (-not $compactBucketOutput) {
    Write-Host 'Status by change type:'
    $items |
        Group-Object Code |
        Sort-Object Name |
        ForEach-Object {
            '{0,6}  {1}' -f $_.Count, $_.Name
        }

    Write-Host ''
    Write-Host 'Status by top-level path:'
    $items |
        Group-Object Top |
        Sort-Object Count -Descending |
        ForEach-Object {
            '{0,6}  {1}' -f $_.Count, $_.Name
        }

    Write-Host ''
    Write-Host 'Most useful detailed views:'
    Write-Host '  git status --short -- app public'
    Write-Host '  git diff --stat -- app public'
    Write-Host '  git ls-files --others --exclude-standard'
    Write-Host '  powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1 -Buckets'
    Write-Host '  powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1 -Bucket "02"'
    Write-Host '  powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1 -Bucket "02" -GitAddCommand'
    Write-Host '  powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1 -ShowLocalOnly'
}

if ($Buckets) {
    Write-Host ''
    Write-Host 'Status by review bucket:'
    $bucketed |
        Group-Object Bucket |
        Sort-Object Name |
        ForEach-Object {
            '{0,6}  {1}' -f $_.Count, $_.Name
        }
}

if ($Bucket) {
    $matches = @(
        $bucketed |
            Where-Object { $_.Bucket -like "$Bucket*" -or $_.Bucket -like "*$Bucket*" } |
            Sort-Object Bucket, Path
    )

    if (-not $matches) {
        Write-Host '  none'
    } elseif ($GitAddCommand) {
        $pathArgs = $matches | ForEach-Object { Format-GitPathArgument -Path $_.Path }
        Write-Host ('git add -- ' + ($pathArgs -join ' '))
    } elseif ($PathsOnly) {
        $matches |
            ForEach-Object {
                $_.Path
            }
    } else {
        Write-Host ''
        Write-Host "Paths in review bucket matching '$Bucket':"
        $matches |
            ForEach-Object {
                '  {0}  {1}' -f $_.Code, $_.Path
            }
    }
}

if ($ShowLocalOnly) {
    Write-Host ''
    Write-Host 'Local-only skip-worktree paths:'
    $localOnly = & git ls-files -v | Where-Object { $_ -match '^S ' }
    if (-not $localOnly) {
        Write-Host '  none'
        exit 0
    }

    $localOnly |
        ForEach-Object { $_.Substring(2) } |
        Group-Object { ($_ -split '[\\/]', 2)[0] } |
        Sort-Object Count -Descending |
        ForEach-Object {
            '{0,6}  {1}' -f $_.Count, $_.Name
        }
}

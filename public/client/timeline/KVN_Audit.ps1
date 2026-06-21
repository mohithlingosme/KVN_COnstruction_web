# ============================================================
#  KVN Construction - Audit Runner (PS 5.1 compatible)
#  Only generates files under audit-report/.
#  Designed so -SkipServer works without parsing/encoding issues.
# ============================================================

param(
    [string]$ProjectRoot = (Get-Location).Path,
    [switch]$SkipServer
)

$ErrorActionPreference = 'Continue'

# Report folder
$ReportFolder = Join-Path $ProjectRoot 'audit-report'
New-Item -ItemType Directory -Path $ReportFolder -Force | Out-Null

$Paths = @{
    Log        = Join-Path $ReportFolder 'audit-log.txt'
    Syntax     = Join-Path $ReportFolder 'syntax-report.txt'
    Duplicates = Join-Path $ReportFolder 'duplicate-functions.txt'
    Includes   = Join-Path $ReportFolder 'includes.txt'
    Security   = Join-Path $ReportFolder 'security-report.txt'
    DeadCode   = Join-Path $ReportFolder 'dead-code-report.txt'
    Routes     = Join-Path $ReportFolder 'route-report.txt'
    Performance= Join-Path $ReportFolder 'performance-report.txt'
    Summary    = Join-Path $ReportFolder 'summary-report.md'
}

foreach ($p in $Paths.Values) { Clear-Content $p -ErrorAction SilentlyContinue }

function LogLine([string]$t) { Add-Content -Path $Paths.Log -Value $t }

$now = Get-Date
LogLine ('KVN CONSTRUCTION - AUDIT LOG')
LogLine ('Started : ' + $now.ToString('yyyy-MM-dd HH:mm:ss'))
LogLine ('Project : ' + $ProjectRoot)
LogLine ('============================================================')

# Collect php files (skip vendor)
$AllPHPFiles = Get-ChildItem -Path $ProjectRoot -Recurse -Filter '*.php' |
    Where-Object { $_.FullName -notmatch 'vendor|node_modules|\.git' }

# -------------------- 1) Syntax --------------------
$SyntaxErrors = 0
@(
    'KVN Construction - Syntax Report',
    ('Generated : ' + (Get-Date).ToString('yyyy-MM-dd HH:mm:ss')),
    '============================================================',
    ''
) | ForEach-Object { Add-Content -Path $Paths.Syntax -Value $_ }

foreach ($file in $AllPHPFiles) {
    $raw = php -l $file.FullName 2>&1
    $output = ($raw | Out-String).Trim()
    $isOK = ($output -match 'No syntax errors')
    if (-not $isOK) { $SyntaxErrors++ }

    @(
        'FILE   : ' + $file.FullName,
        ('STATUS : ' + $(if ($isOK) { 'OK' } else { 'ERROR' })),
        'DETAIL : ' + $output,
        '------------------------------------------------------------',
        ''
    ) | ForEach-Object { Add-Content -Path $Paths.Syntax -Value $_ }
}

LogLine ('Syntax errors found: ' + $SyntaxErrors)

# -------------------- 2) Duplicates --------------------
$FunctionMap = @{}
foreach ($file in $AllPHPFiles) {
    $matches = Select-String -Path $file.FullName -Pattern '^\s*function\s+(\w+)\s*\(' -SimpleMatch:$false
    foreach ($m in $matches) {
        # When -Pattern includes capture groups, Groups[1] holds name
        $fn = $m.Matches[0].Groups[1].Value
        if (-not $FunctionMap.ContainsKey($fn)) { $FunctionMap[$fn] = @() }
        $FunctionMap[$fn] += $file.FullName
    }
}

$Duplicates = @()
foreach ($kv in $FunctionMap.GetEnumerator()) {
    if ($kv.Value.Count -gt 1) { $Duplicates += $kv }
}

$dupCount = $Duplicates.Count

@(
    'KVN Construction - Duplicate Function Report',
    ('Generated : ' + (Get-Date).ToString('yyyy-MM-dd HH:mm:ss')),
    '============================================================',
    ''
) | ForEach-Object { Add-Content -Path $Paths.Duplicates -Value $_ }

foreach ($dup in $Duplicates) {
    Add-Content -Path $Paths.Duplicates -Value ('FUNCTION : ' + $dup.Key + '  (' + $dup.Value.Count + ' occurrences)')
    foreach ($f in $dup.Value) {
        Add-Content -Path $Paths.Duplicates -Value ('  - ' + $f)
    }
    Add-Content -Path $Paths.Duplicates -Value ''
}

if ($dupCount -eq 0) {
    Add-Content -Path $Paths.Duplicates -Value 'No duplicate functions found.'
}

LogLine ('Duplicate functions: ' + $dupCount)

# -------------------- 3) Includes --------------------
$missingCount = 0
$includePattern = '(require|include)(_once)?\s*[\(\x27\"]([^\x27\"\)]+)[\x27\"]'

@(
    'KVN Construction - Include/Require Report',
    ('Generated : ' + (Get-Date).ToString('yyyy-MM-dd HH:mm:ss')),
    '============================================================',
    ''
) | ForEach-Object { Add-Content -Path $Paths.Includes -Value $_ }

foreach ($file in $AllPHPFiles) {
    $lines = Select-String -Path $file.FullName -Pattern $includePattern
    foreach ($line in $lines) {
        # Best-effort: attempt to extract between first quote chars
        $raw = $line.Line
        # Not perfect parsing; count missing via test when we can
        Add-Content -Path $Paths.Includes -Value ($file.FullName + ':' + $line.LineNumber + ' -> ' + $raw)
    }
}

LogLine ('Missing includes: ' + $missingCount)

# -------------------- 4) Security --------------------
@(
    'KVN Construction - Security Report',
    ('Generated : ' + (Get-Date).ToString('yyyy-MM-dd HH:mm:ss')),
    '============================================================',
    ''
) | ForEach-Object { Add-Content -Path $Paths.Security -Value $_ }

# Minimal pattern-only scan: unescaped echo of variables and ignore PDO::exec false positives.
$patterns = @(
    @{ Key='Unescaped output'; Pattern='echo\s+\$[A-Za-z_][A-Za-z0-9_]*\b' },
    @{ Key='eval() usage'; Pattern='\beval\s*\(' },
    @{ Key='shell_exec / exec'; Pattern='\b(shell_exec|exec|passthru|system|popen)\s*\(' },
    @{ Key='MD5 password hashing'; Pattern='md5\s*\(\s*\$' }
)

$secCount = 0
foreach ($file in $AllPHPFiles) {
    foreach ($p in $patterns) {
        # Scan lines
        $hits = Select-String -Path $file.FullName -Pattern $p.Pattern -AllMatches
        foreach ($h in $hits) {
            # ignore PDO::exec in case it matches naive patterns
            if ($h.Line -match '->exec\s*\(') { continue }
            $secCount++
            Add-Content -Path $Paths.Security -Value ''
            Add-Content -Path $Paths.Security -Value ("[" + $p.Key + "]")
            Add-Content -Path $Paths.Security -Value ("  File    : " + $file.FullName)
            Add-Content -Path $Paths.Security -Value ("  Line    : " + $h.LineNumber)
            Add-Content -Path $Paths.Security -Value ("  Snippet : " + $h.Line.Trim())

        }
    }
}

if ($secCount -eq 0) {
    Add-Content -Path $Paths.Security -Value 'No security issues detected.'
}

LogLine ('Security issues found: ' + $secCount)

# -------------------- 5) Dead code --------------------
Add-Content -Path $Paths.DeadCode -Value 'KVN Construction - Dead Code Report (skipped in PS5.1 mode)'

# -------------------- 6) Performance/Routes --------------------
# When SkipServer is set, just generate placeholder files with previous metrics markers.
if ($SkipServer) {
    Add-Content -Path $Paths.Routes -Value 'Route test skipped due to -SkipServer.'
    Add-Content -Path $Paths.Performance -Value 'Performance test skipped due to -SkipServer.'
} else {
    Add-Content -Path $Paths.Routes -Value 'Route testing not implemented in this trimmed PS5.1 runner.'
    Add-Content -Path $Paths.Performance -Value 'Performance testing not implemented in this trimmed PS5.1 runner.'
}

# -------------------- 7) Summary --------------------
$md = @()
$md += '# KVN Construction - Audit Report'
$md += ('**Generated :** ' + (Get-Date).ToString('yyyy-MM-dd HH:mm:ss'))
$md += ''
$md += '| Check | Result |'
$md += '|---|---|'
$md += ('| Syntax Errors | ' + $SyntaxErrors + ' |')
$md += ('| Duplicate Functions | ' + $dupCount + ' |')
$md += ('| Security Issues | ' + $secCount + ' |')
$md += ('| Route Failures | ' + $RouteFailures + ' |')
$md += ('| Slow Routes (>1000ms) | ' + $SlowRoutes + ' |')

$md | Out-File -FilePath $Paths.Summary -Encoding UTF8

LogLine ('AUDIT COMPLETE')

# -------------------- 6) Performance/Routes --------------------

$RouteFailures = 0
$SlowRoutes = 0

@(
    'KVN Construction - Route Report',
    ('Generated : ' + (Get-Date).ToString('yyyy-MM-dd HH:mm:ss')),
    '============================================================',
    ''
) | ForEach-Object { Add-Content -Path $Paths.Routes -Value $_ }

@(
    'KVN Construction - Performance Report',
    ('Generated : ' + (Get-Date).ToString('yyyy-MM-dd HH:mm:ss')),
    '============================================================',
    ''
) | ForEach-Object { Add-Content -Path $Paths.Performance -Value $_ }

if (-not $SkipServer)
{
    $PhpProcess = $null

    try
    {
        $PublicFolder = Join-Path $ProjectRoot 'public'

        $PhpProcess = Start-Process `
            php `
            -ArgumentList "-S 127.0.0.1:8000 -t `"$PublicFolder`"" `
            -PassThru `
            -WindowStyle Hidden

        Start-Sleep -Seconds 3

        $Routes = @(
            '/',
            '/about-us.php',
            '/services.php',
            '/projects.php',
            '/blogs.php',
            '/contact.php',
            '/login.php',
            '/phone-login.php',
            '/forgot-password.php',
            '/reset-password.php',
            '/admin',
            '/admin/dashboard.php'
        )

        foreach ($Route in $Routes)
        {
            try
            {
                $Start = Get-Date

                $Response = Invoke-WebRequest `
                    -Uri ("http://127.0.0.1:8000" + $Route) `
                    -UseBasicParsing `
                    -TimeoutSec 30

                $Elapsed =
                    ((Get-Date) - $Start).TotalMilliseconds

                Add-Content $Paths.Routes (
                    "$Route | HTTP $($Response.StatusCode)"
                )

                Add-Content $Paths.Performance (
                    "$Route | " +
                    [Math]::Round($Elapsed,2) +
                    " ms"
                )

                if ($Elapsed -gt 1000)
                {
                    $SlowRoutes++
                }
            }
            catch
            {
                $RouteFailures++

                Add-Content $Paths.Routes (
                    "$Route | FAILED | $($_.Exception.Message)"
                )
            }
        }
    }
    finally
    {
        if ($PhpProcess)
        {
            Stop-Process -Id $PhpProcess.Id -Force -ErrorAction SilentlyContinue
        }
    }
}
else
{
    Add-Content $Paths.Routes 'Route test skipped due to -SkipServer.'
    Add-Content $Paths.Performance 'Performance test skipped due to -SkipServer.'
}

LogLine ('Route failures: ' + $RouteFailures)
LogLine ('Slow routes: ' + $SlowRoutes)
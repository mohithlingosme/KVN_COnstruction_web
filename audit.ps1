<#
.SYNOPSIS
  KVN Construction Platform - Production Audit Script
.DESCRIPTION
  Generates a complete audit package inside audit-report/.
  Read-only analysis (no application code modifications).
  Idempotent: safe to re-run.
  Works on Windows PowerShell 5.1+.

  OUTPUT MODE (flattened)
  -------------------------
  All generated files are written directly under audit-report/
  (no nested subfolders). File names use underscores to indicate
  logical grouping (e.g. environment_php.md, todo_critical.md).

.NOTES
  - Best-effort detection: continues after errors.
  - Logs every step into audit-report/audit-log.txt.
  - Produces Markdown, JSON summary, and CSV inventories.
  - Optional runtime: can skip PHP server route testing (default: skip).
#>

#requires -Version 5.1
param(
    [string]$ProjectRoot,
    [switch]$IncludeRouteServerTests,
    [switch]$SkipPhpCli,
    [switch]$SkipServer
)

$ErrorActionPreference = 'Continue'

# ============================================================
# GLOBAL STATE
# ============================================================
$script:StartTime = Get-Date
$script:TotalErrors = 0
$script:TotalWarnings = 0
$script:Issues = New-Object System.Collections.Generic.List[object]
$script:Scores = [ordered]@{
    Architecture     = 0
    Security         = 0
    Performance      = 0
    Maintainability  = 0
    CodeQuality      = 0
    Database         = 0
    Documentation    = 0
    Testing          = 0
}
$script:AuditDir = $null
$script:LogFile  = $null
$script:ProjectRoot = $null

# ============================================================
# HELPER FUNCTIONS
# ============================================================

function Write-Color {
    param(
        [Parameter(Mandatory=$true)][string]$Message,
        [Parameter(Mandatory=$true)][ValidateSet('Cyan','Green','Yellow','Red','Magenta','DarkCyan','Gray')][string]$Color,
        [ValidateSet('INFO','OK','WARN','ERROR','HEADER','PROGRESS')][string]$Tag = 'INFO'
    )
    Write-Host "[$Tag] $Message" -ForegroundColor $Color
}

function Write-Log {
    param(
        [Parameter(Mandatory=$true)][string]$Message,
        [ValidateSet('INFO','WARN','ERROR')][string]$Level='INFO'
    )
    $ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    $line = "$ts [$Level] $Message"
    try { Add-Content -Path $script:LogFile -Value $line -ErrorAction SilentlyContinue } catch {}
    if ($Level -eq 'ERROR') { $script:TotalErrors++ }
    if ($Level -eq 'WARN')  { $script:TotalWarnings++ }
}

function Ensure-Dir {
    param([Parameter(Mandatory=$true)][string]$Path)
    if (-not (Test-Path $Path)) {
        New-Item -ItemType Directory -Path $Path -Force | Out-Null
    }
}

function Get-Elapsed {
    $d = (Get-Date) - $script:StartTime
    return "$($d.Hours)h $($d.Minutes)m $($d.Seconds)s"
}

function OutPath {
    param([Parameter(Mandatory=$true)][string]$Name)
    return (Join-Path $script:AuditDir $Name)
}

function Save-Markdown {
    param([Parameter(Mandatory=$true)][string]$Path,[Parameter(Mandatory=$true)][string]$Content)
    Ensure-Dir (Split-Path $Path -Parent)
    try { $Content | Out-File -FilePath $Path -Encoding UTF8 -Force } catch {
        Write-Log "Failed to write $Path : $($_.Exception.Message)" 'WARN'
    }
}

function Save-Json {
    param([Parameter(Mandatory=$true)][string]$Path,[Parameter(Mandatory=$true)][object]$Object)
    Ensure-Dir (Split-Path $Path -Parent)
    try { ($Object | ConvertTo-Json -Depth 10) | Out-File -FilePath $Path -Encoding UTF8 -Force } catch {
        Write-Log "Failed to write JSON $Path : $($_.Exception.Message)" 'WARN'
    }
}

function Save-Csv {
    param(
        [Parameter(Mandatory=$true)][string]$Path,
        [Parameter(Mandatory=$false)][object[]]$Data = @(),
        [Parameter(Mandatory=$true)][string[]]$Properties
    )
    Ensure-Dir (Split-Path $Path -Parent)
    try {
        if ($Data -and $Data.Count -gt 0) {
            $Data | Select-Object $Properties | Export-Csv -Path $Path -NoTypeInformation -Encoding UTF8 -Force
        } else {
            $hdr = ($Properties -join ',')
            Set-Content -Path $Path -Value $hdr -Encoding UTF8 -Force
        }
    } catch {
        Write-Log "Failed to write CSV $Path : $($_.Exception.Message)" 'WARN'
    }
}

function Add-Issue {
    param(
        [Parameter(Mandatory=$true)][string]$Type,
        [Parameter(Mandatory=$true)][ValidateSet('Critical','High','Medium','Low')][string]$Severity,
        [string]$File = '',
        [string]$Message = ''
    )
    $script:Issues.Add([pscustomobject]@{ Type=$Type; Severity=$Severity; File=$File; Message=$Message })
}

function Test-Cmd {
    param([Parameter(Mandatory=$true)][string]$Command)
    return [bool](Get-Command $Command -ErrorAction SilentlyContinue)
}

function Normalize-RelPath {
    param([Parameter(Mandatory=$true)][string]$FullPath)
    if ($script:ProjectRoot -and $FullPath.StartsWith($script:ProjectRoot)) {
        return $FullPath.Substring($script:ProjectRoot.Length).TrimStart('\','/')
    }
    return $FullPath
}

function Get-PhpFiles {
    $exclude = @('vendor','node_modules','.git','.cursor','audit-report')
    try {
        $items = Get-ChildItem -Path $script:ProjectRoot -Recurse -File -Filter '*.php' -ErrorAction SilentlyContinue | Where-Object {
            $ok = $true
            foreach ($e in $exclude) { if ($_.FullName -match [regex]::Escape($e)) { $ok = $false; break } }
            $ok
        }
    } catch { $items = @() }
    return @($items)
}

function Has-PhpCli {
    if ($SkipPhpCli) { return $false }
    return Test-Cmd 'php'
}

function Get-AllFiles {
    try {
        return @(Get-ChildItem -Path $script:ProjectRoot -Recurse -File -ErrorAction SilentlyContinue | Where-Object { $_.FullName -notmatch 'audit-report' })
    } catch { return @() }
}

function Get-FileContent {
    param([Parameter(Mandatory=$true)][string]$Path)
    try { return (Get-Content $Path -Raw -ErrorAction Stop) } catch { return $null }
}

function Get-FileLines {
    param([Parameter(Mandatory=$true)][string]$Path)
    try { return @(Get-Content $Path -ErrorAction Stop) } catch { return @() }
}

# ============================================================
# DETERMINE PROJECT ROOT
# ============================================================
if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $thisScript = $MyInvocation.MyCommand.Path
    if ($thisScript) {
        $ProjectRoot = (Split-Path -Path $thisScript -Parent)
    } else {
        $ProjectRoot = (Get-Location).Path
    }
}
$script:ProjectRoot = $ProjectRoot

# ============================================================
# OUTPUT FOLDER (flattened - single folder)
# ============================================================
$script:AuditDir = Join-Path $script:ProjectRoot 'audit-report'
Ensure-Dir $script:AuditDir

$script:LogFile = Join-Path $script:AuditDir 'audit-log.txt'
try { "=== KVN Construction Audit Log ===" | Out-File -FilePath $script:LogFile -Encoding UTF8 -Force } catch {}

Write-Color "ProjectRoot: $script:ProjectRoot" -Color Green 'OK'
Write-Color "Output: $script:AuditDir" -Color Green 'OK'
Write-Log "Audit start. IncludeRouteServerTests=$IncludeRouteServerTests SkipPhpCli=$SkipPhpCli SkipServer=$SkipServer" 'INFO'
Write-Color "Execution time: starting..." -Color DarkCyan 'PROGRESS'

# ============================================================
# PHASE 1: ENVIRONMENT REPORT
# ============================================================
function Invoke-Environment {
    Write-Color 'Phase 1 - Environment' -Color Magenta 'HEADER'
    Write-Log 'Starting environment audit' 'INFO'

    # --- PHP ---
    $phpVersion = 'Not Found'
    $phpIni = 'Not Found'
    $phpExts = @()
    $composerVersion = 'Not Found'
    $nodeVersion = 'Not Found'
    $npmVersion = 'Not Found'

    if (Has-PhpCli) {
        try {
            $phpVersion = (php -v 2>&1 | Select-Object -First 1)
            $iniRaw = (php --ini 2>&1 | Out-String).Trim()
            $m = $iniRaw | Select-String -Pattern 'Loaded Configuration File'
            if ($m) { $phpIni = (($m.Line -split ':',2)[1]).Trim() }
            $phpExts = @(php -m 2>&1)
        } catch { Write-Log "PHP env error: $($_.Exception.Message)" 'WARN' }
    } else {
        $phpVersion = 'PHP CLI not found'
    }

    if (Test-Cmd 'composer') { try { $composerVersion = (composer --version 2>&1 | Out-String).Trim() } catch {} }
    if (Test-Cmd 'node') { try { $nodeVersion = (node --version 2>&1 | Out-String).Trim() } catch {} }
    if (Test-Cmd 'npm') { try { $npmVersion = (npm --version 2>&1 | Out-String).Trim() } catch {} }

    $md = @()
    $md += '# PHP Environment'
    $md += ''
    $md += '| Property | Value |'
    $md += '|---|---|'
    $md += "| PHP Version | $phpVersion |"
    $md += "| Loaded php.ini | $phpIni |"
    $md += "| Composer Version | $composerVersion |"
    $md += "| Node Version | $nodeVersion |"
    $md += "| NPM Version | $npmVersion |"
    $md += ''
    $md += "## Enabled PHP Extensions ($($phpExts.Count))"
    $md += '```'
    if ($phpExts.Count -gt 0) { $md += ($phpExts -join "`n") } else { $md += 'N/A' }
    $md += '```'
    Save-Markdown (OutPath 'environment_php.md') ($md -join "`n")

    # --- Apache ---
    $apacheVersion = 'Not Found'
    try {
        if (Test-Cmd 'httpd') { $apacheVersion = (httpd -v 2>&1 | Select-Object -First 1) }
        elseif (Test-Path 'C:\xampp\apache\bin\httpd.exe') { $apacheVersion = 'XAMPP httpd detected (httpd.exe present)' }
    } catch {}

    $vp = Join-Path $script:ProjectRoot 'docker\apache\vhost.conf'
    $md = @()
    $md += '# Apache Environment'
    $md += ''
    $md += '| Property | Value |'
    $md += '|---|---|'
    $md += "| Apache Version | $apacheVersion |"
    $md += "| Virtual Host Config | docker/apache/vhost.conf |"
    $md += ''
    if (Test-Path $vp) {
        $md += '## Virtual Host Configuration'
        $md += '```apache'
        $md += ((Get-Content $vp -Raw -ErrorAction SilentlyContinue).Trim())
        $md += '```'
    } else {
        $md += '_Virtual host config file missing in repository._'
    }
    Save-Markdown (OutPath 'environment_apache.md') ($md -join "`n")

    # --- MySQL ---
    $mysqlVersion = 'Not Found'
    if (Test-Cmd 'mysql') {
        try { $mysqlVersion = (mysql --version 2>&1 | Out-String).Trim() } catch {}
    } else {
        if (Test-Path 'C:\xampp\mysql\bin\mysql.exe') { $mysqlVersion = 'XAMPP mysql detected (mysql.exe present)' }
    }
    $md = @()
    $md += '# MySQL Environment'
    $md += ''
    $md += '| Property | Value |'
    $md += '|---|---|'
    $md += "| MySQL Version | $mysqlVersion |"
    $md += "| Binary | $(if(Test-Cmd 'mysql'){'mysql in PATH'}elseif(Test-Path 'C:\xampp\mysql\bin\mysql.exe'){'C:\xampp\mysql\bin\mysql.exe'}else{'Not found'}) |"
    Save-Markdown (OutPath 'environment_mysql.md') ($md -join "`n")

    # --- Extensions ---
    $md = @()
    $md += '# PHP Extensions Detail'
    $md += ''
    $md += "Total extensions loaded: $($phpExts.Count)"
    $md += ''
    $md += '## Extension List'
    $md += '```'
    if ($phpExts.Count -gt 0) { $md += ($phpExts -join "`n") } else { $md += 'N/A' }
    $md += '```'
    Save-Markdown (OutPath 'environment_extensions.md') ($md -join "`n")

    # --- OS / System ---
    $md = @()
    $md += '# Operating System & Runtime'
    $md += ''
    $md += '| Property | Value |'
    $md += '|---|---|'
    try {
        $os = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
        if ($os) { $md += "| OS | $($os.Caption) |" } else { $md += '| OS | Unknown |' }
    } catch { $md += '| OS | Unknown |' }
    try {
        $mem = Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue
        if ($mem -and $mem.TotalPhysicalMemory) { $gb = [math]::Round($mem.TotalPhysicalMemory/1GB,1); $md += "| Memory | ${gb}GB |" } else { $md += '| Memory | Unknown |' }
    } catch { $md += '| Memory | Unknown |' }
    try {
        $tz = Get-TimeZone
        if ($tz) { $md += "| Timezone | $($tz.Id) |" } else { $md += '| Timezone | Unknown |' }
    } catch { $md += '| Timezone | Unknown |' }
    try {
        $drive = (Split-Path $script:ProjectRoot -Qualifier).TrimEnd('\')
        $psd = Get-PSDrive -Name $drive -ErrorAction SilentlyContinue
        if ($psd) {
            $free = [math]::Round($psd.Free/1GB,1)
            $total = [math]::Round(($psd.Used+$psd.Free)/1GB,1)
            $md += "| Disk Space | ${free}GB free / ${total}GB total |"
        } else { $md += '| Disk Space | Unknown |' }
    } catch { $md += '| Disk Space | Unknown |' }
    $md += "| Project Root | $($script:ProjectRoot) |"
    Save-Markdown (OutPath 'environment_os.md') ($md -join "`n")

    # --- Production Readiness ---
    $ef = Join-Path $script:ProjectRoot '.env'
    $ht = Join-Path $script:ProjectRoot '.htaccess'
    $robots = Join-Path $script:ProjectRoot 'public\robots.txt'
    $sitemap = Join-Path $script:ProjectRoot 'public\sitemap.xml'
    $docker = Join-Path $script:ProjectRoot 'docker-compose.yml'
    $deploy = Join-Path $script:ProjectRoot 'deploy.sh'
    $readme = Join-Path $script:ProjectRoot 'README.md'

    $md = @()
    $md += '# Production Readiness Assessment'
    $md += ''
    $md += '| Check | Status | Notes |'
    $md += '|---|---|---|'
    $md += "| .env | $(if(Test-Path $ef){'PRESENT'}else{'MISSING'}) | $(if(Test-Path $ef){'Environment variables configured'}else{'Missing - critical for production'}) |"
    $md += "| .htaccess | $(if(Test-Path $ht){'PRESENT'}else{'MISSING'}) | $(if(Test-Path $ht){'URL rewriting configured'}else{'Missing - may affect routing'}) |"
    $md += "| robots.txt | $(if(Test-Path $robots){'PRESENT'}else{'MISSING'}) | $(if(Test-Path $robots){'Search engine crawling configured'}else{'Missing - SEO impact'}) |"
    $md += "| sitemap.xml | $(if(Test-Path $sitemap){'PRESENT'}else{'MISSING'}) | $(if(Test-Path $sitemap){'Search engine indexing configured'}else{'Missing - SEO impact'}) |"
    $md += "| docker-compose.yml | $(if(Test-Path $docker){'PRESENT'}else{'MISSING'}) | $(if(Test-Path $docker){'Container orchestration configured'}else{'Missing'}) |"
    $md += "| deploy.sh | $(if(Test-Path $deploy){'PRESENT'}else{'MISSING'}) | $(if(Test-Path $deploy){'Deployment script present'}else{'Missing'}) |"
    $md += "| README.md | $(if(Test-Path $readme){'PRESENT'}else{'MISSING'}) | $(if(Test-Path $readme){'Documentation present'}else{'Missing'}) |"
    $md += ''
    $md += '## Recommendations'
    $md += '1. Ensure .env is properly configured with production credentials'
    $md += '2. Verify .htaccess rules for production environment'
    $md += '3. Add robots.txt and sitemap.xml for SEO'
    $md += '4. Set up automated deployment pipeline'
    $md += '5. Enable HTTPS and configure SSL certificates'
    Save-Markdown (OutPath 'environment_production-readiness.md') ($md -join "`n")

    Write-Log 'Environment audit complete' 'INFO'
}

# ============================================================
# PHASE 2: PROJECT INVENTORY
# ============================================================
function Invoke-Inventory {
    Write-Color 'Phase 2 - Inventory' -Color Magenta 'HEADER'
    Write-Log 'Starting project inventory' 'INFO'

    $allFiles = Get-AllFiles
    $phpFiles = Get-PhpFiles

    # PHP Files CSV
    $phpRows = @()
    foreach ($f in $phpFiles) {
        $phpRows += [pscustomobject]@{
            Name          = $f.Name
            Length        = $f.Length
            LastWriteTime = $f.LastWriteTime.ToString('yyyy-MM-dd HH:mm:ss')
            FullName      = $f.FullName
            Relative      = (Normalize-RelPath $f.FullName)
        }
    }
    Save-Csv (OutPath 'inventory_php_files.csv') $phpRows @('Name','Length','LastWriteTime','Relative','FullName')

    # Controllers
    $controllers = @($allFiles | Where-Object { $_.FullName -match '\\app\\controllers\\' -and $_.Extension -eq '.php' })
    Save-Csv (OutPath 'inventory_controllers.csv') (ToInv $controllers) @('Relative','Length','LastWriteTime','FullName')

    # Models
    $models = @($allFiles | Where-Object { $_.FullName -match '\\app\\models\\' -and $_.Extension -eq '.php' })
    Save-Csv (OutPath 'inventory_models.csv') (ToInv $models) @('Relative','Length','LastWriteTime','FullName')

    # Views
    $views = @($allFiles | Where-Object { $_.FullName -match '\\app\\views\\' -and $_.Extension -eq '.php' })
    Save-Csv (OutPath 'inventory_views.csv') (ToInv $views) @('Relative','Length','LastWriteTime','FullName')

    # Helpers
    $helpers = @($allFiles | Where-Object { $_.FullName -match '\\helpers\\' -and $_.Extension -eq '.php' })
    Save-Csv (OutPath 'inventory_helpers.csv') (ToInv $helpers) @('Relative','Length','LastWriteTime','FullName')

    # Middleware
    $middleware = @($allFiles | Where-Object { $_.FullName -match '\\middleware\\' -and $_.Extension -eq '.php' })
    Save-Csv (OutPath 'inventory_middleware.csv') (ToInv $middleware) @('Relative','Length','LastWriteTime','FullName')

    # Config files
    $configFiles = @($allFiles | Where-Object { $_.FullName -match '\\config\\' -and ($_.Extension -eq '.php' -or $_.Extension -eq '.json') })
    Save-Csv (OutPath 'inventory_config.csv') (ToInv $configFiles) @('Relative','Length','LastWriteTime','FullName')

    # Assets
    $assetsDir = Join-Path $script:ProjectRoot 'public\assets'
    $images = @(); $js = @(); $css = @(); $fonts = @()
    if (Test-Path $assetsDir) {
        $images = @(Get-ChildItem $assetsDir -Recurse -File -Include '*.png','*.jpg','*.jpeg','*.gif','*.svg','*.webp','*.ico' -ErrorAction SilentlyContinue)
        $js     = @(Get-ChildItem $assetsDir -Recurse -File -Filter '*.js' -ErrorAction SilentlyContinue)
        $css    = @(Get-ChildItem $assetsDir -Recurse -File -Filter '*.css' -ErrorAction SilentlyContinue)
        $fonts  = @(Get-ChildItem $assetsDir -Recurse -File -Include '*.woff','*.woff2','*.ttf','*.eot','*.otf' -ErrorAction SilentlyContinue)
    }
    Save-Csv (OutPath 'inventory_images.csv') (ToInv $images) @('Relative','Size','LastWriteTime')
    Save-Csv (OutPath 'inventory_js.csv') (ToInv $js) @('Relative','Size','LastWriteTime')
    Save-Csv (OutPath 'inventory_css.csv') (ToInv $css) @('Relative','Size','LastWriteTime')
    Save-Csv (OutPath 'inventory_fonts.csv') (ToInv $fonts) @('Relative','Size','LastWriteTime')

    # SQL, JSON, Uploads
    $sqlFiles = @($allFiles | Where-Object { $_.Extension -eq '.sql' })
    $jsonFiles = @($allFiles | Where-Object { $_.Extension -eq '.json' })
    $uploadsDir = Join-Path $script:ProjectRoot 'public\uploads'
    $uploads = @(); if (Test-Path $uploadsDir) { $uploads = @(Get-ChildItem $uploadsDir -Recurse -File -ErrorAction SilentlyContinue) }

    Save-Csv (OutPath 'inventory_sql.csv') (ToInv $sqlFiles) @('Relative','Size','LastWriteTime')
    Save-Csv (OutPath 'inventory_json.csv') (ToInv $jsonFiles) @('Relative','Size','LastWriteTime')
    Save-Csv (OutPath 'inventory_uploads.csv') (ToInv $uploads) @('Relative','Size','LastWriteTime')

    # Markdown summary
    $md = @()
    $md += '# Project Inventory'
    $md += ''
    $md += '| Metric | Value |'
    $md += '|---|---|'
    $md += "| Total Files | $($allFiles.Count) |"
    $md += "| PHP Files | $($phpFiles.Count) |"
    $md += "| Controllers | $($controllers.Count) |"
    $md += "| Models | $($models.Count) |"
    $md += "| Views | $($views.Count) |"
    $md += "| Helpers | $($helpers.Count) |"
    $md += "| Middleware | $($middleware.Count) |"
    $md += "| Config Files | $($configFiles.Count) |"
    $md += "| Images | $($images.Count) |"
    $md += "| JavaScript | $($js.Count) |"
    $md += "| CSS | $($css.Count) |"
    $md += "| Fonts | $($fonts.Count) |"
    $md += "| SQL Files | $($sqlFiles.Count) |"
    $md += "| JSON Files | $($jsonFiles.Count) |"
    $md += "| Uploads | $($uploads.Count) |"
    $md += ''
    $md += '## File Size Summary'
    $md += ''
    $md += '| Category | Total Size (KB) |'
    $md += '|---|---:|'
    $md += "| PHP | $([math]::Round((($phpFiles|Measure-Object Length -Sum).Sum)/1KB,1)) |"
    $md += "| Images | $([math]::Round((($images|Measure-Object Length -Sum).Sum)/1KB,1)) |"
    $md += "| JavaScript | $([math]::Round((($js|Measure-Object Length -Sum).Sum)/1KB,1)) |"
    $md += "| CSS | $([math]::Round((($css|Measure-Object Length -Sum).Sum)/1KB,1)) |"
    $md += "| SQL | $([math]::Round((($sqlFiles|Measure-Object Length -Sum).Sum)/1KB,1)) |"
    $md += "| Uploads | $([math]::Round((($uploads|Measure-Object Length -Sum).Sum)/1KB,1)) |"
    Save-Markdown (OutPath 'project_inventory.md') ($md -join "`n")

    Write-Log 'Inventory audit complete' 'INFO'
}

function ToInv {
    param($arr)
    return @($arr | ForEach-Object {
        [pscustomobject]@{
            Relative      = Normalize-RelPath $_.FullName
            FullName      = $_.FullName
            Size          = $_.Length
            LastWriteTime = $_.LastWriteTime.ToString('yyyy-MM-dd HH:mm:ss')
        }
    })
}

# ============================================================
# PHASE 3: PHP ANALYSIS
# ============================================================
function Invoke-PhpAudit {
    Write-Color 'Phase 3 - PHP Analysis' -Color Magenta 'HEADER'
    Write-Log 'Starting PHP analysis' 'INFO'

    $files = Get-PhpFiles
    $syntaxErrors = @()
    $funcs = @{}
    $classes = @{}
    $namespaces = @{}
    $missingIncludes = @()
    $deprecatedMatches = @()

    if (-not $files -or $files.Count -eq 0) {
        Save-Markdown (OutPath 'php_analysis.md') "# PHP Analysis`n`n_No PHP files found._"
        Save-Markdown (OutPath 'reports_php-errors.md') "# PHP Errors`n`nNo PHP files found."
        return
    }

    $i = 0
    foreach ($f in $files) {
        $i++
        if (($i % 25) -eq 0) { Write-Color "Linted $i/$($files.Count) PHP files" -Color DarkCyan 'PROGRESS' }

        # Syntax check via php -l
        if (Has-PhpCli) {
            try {
                $out = @(php -l $f.FullName 2>&1)
                $s = ($out | Out-String).Trim()
                if ($s -notmatch 'No syntax errors') {
                    $syntaxErrors += [pscustomobject]@{ File = Normalize-RelPath $f.FullName; Error = $s }
                    Add-Issue -Type 'PHP_SYNTAX' -Severity 'High' -File (Normalize-RelPath $f.FullName) -Message $s
                }
            } catch {
                $syntaxErrors += [pscustomobject]@{ File = Normalize-RelPath $f.FullName; Error = $($_.Exception.Message) }
                Add-Issue -Type 'PHP_LINT' -Severity 'High' -File (Normalize-RelPath $f.FullName) -Message $($_.Exception.Message)
            }
        } else {
            Add-Issue -Type 'PHP_CLI_MISSING' -Severity 'High' -File 'php' -Message 'php executable not found; php -l skipped.'
            break
        }

        # Static analysis
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop

            # Function definitions
            $m = [regex]::Matches($content, '(?m)^\s*function\s+(?:&\s*)?([A-Za-z_][A-Za-z0-9_]*)\s*\(')
            foreach ($mm in $m) {
                $name = $mm.Groups[1].Value
                if (-not $funcs.ContainsKey($name)) { $funcs[$name] = @() }
                $funcs[$name] += (Normalize-RelPath $f.FullName)
            }

            # Class definitions
            $mc = [regex]::Matches($content, '(?m)^\s*class\s+([A-Za-z_][A-Za-z0-9_]*)\b')
            foreach ($mm in $mc) {
                $name = $mm.Groups[1].Value
                if (-not $classes.ContainsKey($name)) { $classes[$name] = @() }
                $classes[$name] += (Normalize-RelPath $f.FullName)
            }

            # Namespace declarations
            $mn = [regex]::Matches($content, '(?m)^\s*namespace\s+([A-Za-z_][A-Za-z0-9_\\]*)\s*;')
            foreach ($mm in $mn) {
                $ns = $mm.Groups[1].Value
                if (-not $namespaces.ContainsKey($ns)) { $namespaces[$ns] = @() }
                $namespaces[$ns] += (Normalize-RelPath $f.FullName)
            }

            # Deprecated functions
            $deprecatedFns = @('mysql_connect','mysql_query','mysql_fetch_array','mysql_fetch_assoc','mysql_fetch_row','mysql_num_rows','mysql_select_db','mysql_error','create_function','call_user_method','call_user_method_array','ereg','eregi','ereg_replace','eregi_replace','split','spliti','each','set_magic_quotes_runtime')
            foreach ($df in $deprecatedFns) {
                if ($content -match [regex]::Escape($df)) {
                    $deprecatedMatches += [pscustomobject]@{ File = Normalize-RelPath $f.FullName; Function = $df }
                    Add-Issue -Type 'DEPRECATED_FUNCTION' -Severity 'Medium' -File (Normalize-RelPath $f.FullName) -Message "Deprecated function usage: $df"
                }
            }

            # Missing includes / require
            $incMatches = [regex]::Matches($content, '(?i)(include|require|include_once|require_once)\s*\(?\s*[""]([^""]+)[""]')
            foreach ($mm in $incMatches) {
                $incPath = $mm.Groups[2].Value
                # Check if the included file exists relative to project root
                $resolved = Join-Path $script:ProjectRoot $incPath
                if (-not (Test-Path $resolved)) {
                    # Try relative to the file's directory
                    $fileDir = Split-Path $f.FullName -Parent
                    $resolved2 = Join-Path $fileDir $incPath
                    if (-not (Test-Path $resolved2)) {
                        $missingIncludes += [pscustomobject]@{ File = Normalize-RelPath $f.FullName; Include = $incPath }
                        Add-Issue -Type 'MISSING_INCLUDE' -Severity 'High' -File (Normalize-RelPath $f.FullName) -Message "Missing include/require: $incPath"
                    }
                }
            }

        } catch {}
    }

    $dupFuncs = $funcs.GetEnumerator() | Where-Object { $_.Value.Count -gt 1 }
    $dupClasses = $classes.GetEnumerator() | Where-Object { $_.Value.Count -gt 1 }

    $md = @()
    $md += '# PHP Analysis'
    $md += ''
    $md += "## Summary"
    $md += ''
    $md += '| Metric | Value |'
    $md += '|---|---|'
    $md += "| PHP Files Scanned | $($files.Count) |"
    $md += "| Syntax Errors | $($syntaxErrors.Count) |"
    $md += "| Duplicate Functions | $($dupFuncs.Count) |"
    $md += "| Duplicate Classes | $($dupClasses.Count) |"
    $md += "| Namespaces Found | $($namespaces.Count) |"
    $md += "| Deprecated Function Usages | $($deprecatedMatches.Count) |"
    $md += "| Missing Includes/Requires | $($missingIncludes.Count) |"

    if ($syntaxErrors.Count -gt 0) {
        $md += ''
        $md += '## Syntax Errors'
        $md += ''
        $md += '| File | Error |'
        $md += '|---|---|'
        foreach ($e in $syntaxErrors) { $safe = ($e.Error -replace '\|','/'); $md += "| $($e.File) | $safe |" }
    }

    if ($dupFuncs.Count -gt 0) {
        $md += ''
        $md += '## Duplicate Functions'
        $md += ''
        $md += '| Function | Files |'
        $md += '|---|---|'
        foreach ($d in $dupFuncs) { $md += "| $($d.Key) | $([string]::Join('; ', $d.Value)) |" }
    }

    if ($dupClasses.Count -gt 0) {
        $md += ''
        $md += '## Duplicate Classes'
        $md += ''
        $md += '| Class | Files |'
        $md += '|---|---|'
        foreach ($d in $dupClasses) { $md += "| $($d.Key) | $([string]::Join('; ', $d.Value)) |" }
    }

    if ($namespaces.Count -gt 0) {
        $md += ''
        $md += '## Namespaces'
        $md += ''
        $md += '| Namespace | Files |'
        $md += '|---|---|'
        foreach ($n in $namespaces.GetEnumerator()) { $md += "| $($n.Key) | $($n.Value.Count) file(s) |" }
    }

    if ($deprecatedMatches.Count -gt 0) {
        $md += ''
        $md += '## Deprecated Function Usage'
        $md += ''
        $md += '| File | Function |'
        $md += '|---|---|'
        foreach ($d in $deprecatedMatches) { $md += "| $($d.File) | $($d.Function) |" }
    }

    if ($missingIncludes.Count -gt 0) {
        $md += ''
        $md += '## Missing Includes / Requires'
        $md += ''
        $md += '| File | Missing Path |'
        $md += '|---|---|'
        foreach ($m in $missingIncludes) { $md += "| $($m.File) | $($m.Include) |" }
    }

    $md += ''
    $md += '## Recommendations'
    $md += '1. Fix all syntax errors before deployment'
    $md += '2. Remove duplicate function and class definitions'
    $md += '3. Replace deprecated functions with modern alternatives'
    $md += '4. Resolve all missing include/require statements'
    $md += '5. Adopt PSR-4 autoloading for better namespace management'

    Save-Markdown (OutPath 'php_analysis.md') ($md -join "`n")

    # PHP Errors report
    $phpErrText = "# PHP Errors`n`n"
    if ($syntaxErrors.Count -gt 0) {
        $phpErrText += "Found $($syntaxErrors.Count) syntax errors."
        $phpErrText += "`n`n| File | Error |`n|---|---|"
        foreach ($e in $syntaxErrors) { $safe = ($e.Error -replace '\|','/'); $phpErrText += "`n| $($e.File) | $safe |" }
    } else {
        $phpErrText += "No syntax errors found."
    }
    Save-Markdown (OutPath 'reports_php-errors.md') $phpErrText

    Write-Log 'PHP audit complete' 'INFO'
}

# ============================================================
# PHASE 4: DATABASE ANALYSIS
# ============================================================
function Invoke-Database {
    Write-Color 'Phase 4 - Database' -Color Magenta 'HEADER'
    Write-Log 'Starting database analysis' 'INFO'

    $sqlFiles = @()
    try {
        $sqlFiles = Get-ChildItem -Path $script:ProjectRoot -Recurse -File -Filter '*.sql' -ErrorAction SilentlyContinue | Where-Object { $_.FullName -notmatch 'node_modules|audit-report|vendor/.+sql' }
    } catch {}

    $tables = @{}
    $columns = @{}
    $indexes = @{}
    $foreignKeys = @{}
    $views = @()
    $procedures = @()
    $triggers = @()
    $autoIncrements = @()

    foreach ($sf in $sqlFiles) {
        try {
            $content = Get-Content $sf.FullName -Raw -ErrorAction Stop

            # CREATE TABLE
            $tableMatches = [regex]::Matches($content, '(?i)create\s+table\s+(?:if\s+not\s+exists\s+)?[`"\[]?([A-Za-z0-9_]+)[`"\]]?\s*\(')
            foreach ($m in $tableMatches) {
                $tname = $m.Groups[1].Value
                if (-not $tables.ContainsKey($tname)) { $tables[$tname] = @() }
                $tables[$tname] += Normalize-RelPath $sf.FullName
            }

            # Columns
            $colMatches = [regex]::Matches($content, '(?i)create\s+table.*?\(([^)]+)\)', [System.Text.RegularExpressions.RegexOptions]::Singleline)
            foreach ($m in $colMatches) {
                $colBlock = $m.Groups[1].Value
                $colLines = $colBlock -split ','
                foreach ($cl in $colLines) {
                    $cl = $cl.Trim()
                    if ($cl -match '(?i)^\s*[`"\[]?([A-Za-z0-9_]+)[`"\]]?\s+') {
                        $cname = $matches[1]
                        if (-not $columns.ContainsKey($cname)) { $columns[$cname] = @() }
                        $columns[$cname] += $cl.Trim()
                    }
                }
            }

            # Indexes
            $idxMatches = [regex]::Matches($content, '(?i)(?:create\s+(?:unique\s+)?index|index|key)\s+[`"\[]?([A-Za-z0-9_]+)[`"\]]?\s*(?:using\s+\w+\s*)?\([^)]+\)')
            foreach ($m in $idxMatches) { $indexes[$m.Groups[1].Value] = Normalize-RelPath $sf.FullName }

            # Foreign Keys
            $fkMatches = [regex]::Matches($content, '(?i)foreign\s+key\s*\([^)]+\)\s*references\s+[`"\[]?([A-Za-z0-9_]+)[`"\]]?\s*\([^)]+\)')
            foreach ($m in $fkMatches) { $foreignKeys[$m.Groups[1].Value] = Normalize-RelPath $sf.FullName }

            # Views
            $viewMatches = [regex]::Matches($content, '(?i)create\s+(?:or\s+replace\s+)?view\s+[`"\[]?([A-Za-z0-9_]+)[`"\]]?')
            foreach ($m in $viewMatches) { $views += $m.Groups[1].Value }

            # Procedures
            $procMatches = [regex]::Matches($content, '(?i)create\s+(?:or\s+replace\s+)?procedure\s+[`"\[]?([A-Za-z0-9_]+)[`"\]]?')
            foreach ($m in $procMatches) { $procedures += $m.Groups[1].Value }

            # Triggers
            $trigMatches = [regex]::Matches($content, '(?i)create\s+(?:or\s+replace\s+)?trigger\s+[`"\[]?([A-Za-z0-9_]+)[`"\]]?')
            foreach ($m in $trigMatches) { $triggers += $m.Groups[1].Value }

            # Auto Increment
            $aiMatches = [regex]::Matches($content, '(?i)auto_increment')
            if ($aiMatches.Count -gt 0) { $autoIncrements += Normalize-RelPath $sf.FullName }

        } catch {}
    }

    $uniqueTables = @($tables.Keys | Sort-Object)
    $uniqueColumns = @($columns.Keys | Sort-Object)

    $md = @()
    $md += '# Database Analysis'
    $md += ''
    $md += '## Summary'
    $md += ''
    $md += '| Metric | Value |'
    $md += '|---|---|'
    $md += "| SQL Files Scanned | $($sqlFiles.Count) |"
    $md += "| Tables Detected | $($uniqueTables.Count) |"
    $md += "| Unique Columns | $($uniqueColumns.Count) |"
    $md += "| Indexes | $($indexes.Count) |"
    $md += "| Foreign Keys | $($foreignKeys.Count) |"
    $md += "| Views | $($views.Count) |"
    $md += "| Stored Procedures | $($procedures.Count) |"
    $md += "| Triggers | $($triggers.Count) |"
    $md += "| Files with AUTO_INCREMENT | $($autoIncrements.Count) |"

    if ($uniqueTables.Count -gt 0) {
        $md += ''
        $md += '## Tables'
        $md += ''
        $md += '| Table | Source File(s) |'
        $md += '|---|---|'
        foreach ($t in $uniqueTables) { $md += "| $t | $([string]::Join('; ', $tables[$t])) |" }
    }

    if ($uniqueColumns.Count -gt 0) {
        $md += ''
        $md += '## Columns (sample)'
        $md += ''
        $md += '| Column | Definition |'
        $md += '|---|---|'
        $i = 0
        foreach ($c in $uniqueColumns) {
            if ($i -ge 50) { $md += "| ... | ($($uniqueColumns.Count - 50) more columns) |"; break }
            $md += "| $c | $($columns[$c][0]) |"
            $i++
        }
    }

    if ($indexes.Count -gt 0) {
        $md += ''
        $md += '## Indexes'
        $md += ''
        $md += '| Index | Source File |'
        $md += '|---|---|'
        foreach ($ix in $indexes.Keys) { $md += "| $ix | $($indexes[$ix]) |" }
    }

    if ($foreignKeys.Count -gt 0) {
        $md += ''
        $md += '## Foreign Keys'
        $md += ''
        $md += '| Referenced Table | Source File |'
        $md += '|---|---|'
        foreach ($fk in $foreignKeys.Keys) { $md += "| $fk | $($foreignKeys[$fk]) |" }
    }

    if ($views.Count -gt 0) {
        $md += ''
        $md += '## Views'
        $md += ''
        $md += ($views -join ', ')
    }

    if ($procedures.Count -gt 0) {
        $md += ''
        $md += '## Stored Procedures'
        $md += ''
        $md += ($procedures -join ', ')
    }

    if ($triggers.Count -gt 0) {
        $md += ''
        $md += '## Triggers'
        $md += ''
        $md += ($triggers -join ', ')
    }

    $md += ''
    $md += '## Recommendations'
    $md += '1. Ensure all tables have proper indexes for query performance'
    $md += '2. Verify foreign key relationships are properly defined'
    $md += '3. Consider normalization improvements for large tables'
    $md += '4. Add missing indexes on frequently queried columns'
    $md += '5. Review AUTO_INCREMENT starting values for production'

    Save-Markdown (OutPath 'database_analysis.md') ($md -join "`n")

    # ER Summary
    $er = @()
    $er += '# Entity-Relationship Summary (Best-Effort)'
    $er += ''
    $er += 'This audit parses CREATE TABLE statements from .sql files to extract schema information.'
    $er += ''
    $er += "**Tables:** $($uniqueTables.Count)"
    $er += ''
    $er += '## Table List'
    $er += ''
    $er += '```'
    $er += ($uniqueTables -join "`n")
    $er += '```'
    $er += ''
    $er += '## Relationships (Foreign Keys)'
    if ($foreignKeys.Count -gt 0) {
        $er += ''
        $er += '| Referenced Table | Source File |'
        $er += '|---|---|'
        foreach ($fk in $foreignKeys.Keys) { $er += "| $fk | $($foreignKeys[$fk]) |" }
    } else {
        $er += ''
        $er += '_No foreign key relationships detected._'
    }
    Save-Markdown (OutPath 'database_er_summary.md') ($er -join "`n")

    # Schema
    $schema = @()
    $schema += '# Database Schema'
    $schema += ''
    $schema += "Generated from $($sqlFiles.Count) SQL files."
    $schema += ''
    $schema += '## Tables'
    $schema += ''
    $schema += '```'
    $schema += ($uniqueTables -join "`n")
    $schema += '```'
    Save-Markdown (OutPath 'database_schema.md') ($schema -join "`n")

    Write-Log 'Database analysis complete' 'INFO'
}

# ============================================================
# PHASE 5: SECURITY ANALYSIS
# ============================================================
function Invoke-Security {
    Write-Color 'Phase 5 - Security' -Color Magenta 'HEADER'
    Write-Log 'Starting security analysis' 'INFO'

    $secFiles = @(
        'helpers\csrf.php',
        'helpers\security.php',
        'helpers\auth.php',
        'helpers\session.php',
        'helpers\rateLimiter.php',
        'helpers\upload.php',
        'helpers\otp.php',
        'helpers\functions_security.php',
        'helpers\functions.php',
        'middleware\auth.php',
        'middleware\admin.php',
        'middleware\client.php',
        'middleware\guest.php',
        'middleware\admin-auth.php',
        'middleware\admin-guest.php',
        'middleware\clients.php',
        'config\app.php'
    )

    $md = @()
    $md += '# Security Analysis (Heuristic)'
    $md += ''
    $md += '## Security Components'
    $md += ''
    $md += '| Component | Status |'
    $md += '|---|---|'
    foreach ($sf in $secFiles) {
        $full = Join-Path $script:ProjectRoot $sf
        $status = if (Test-Path $full) { 'Present' } else { 'Missing' }
        $md += "| $sf | $status |"
        if ($status -eq 'Missing') {
            Add-Issue -Type 'SECURITY_COMPONENT_MISSING' -Severity 'High' -File $sf -Message "Missing security component: $sf"
        }
    }

    # Heuristic pattern scanning
    $patterns = @(
        @{ Type = 'XSS (direct echo of variable)'; Pattern = 'echo\s+\$[A-Za-z_][A-Za-z0-9_]*\b' }
        @{ Type = 'eval() usage'; Pattern = '\beval\s*\(' }
        @{ Type = 'exec/system/shell_exec/passthru'; Pattern = '\b(shell_exec|exec|passthru|system|popen)\s*\(' }
        @{ Type = 'md5 hash (insecure)'; Pattern = '\bmd5\s*\(\s*\$' }
        @{ Type = 'sha1 hash (insecure)'; Pattern = '\bsha1\s*\(\s*\$' }
        @{ Type = 'mysql_* functions (deprecated)'; Pattern = '\bmysql_\w+\s*\(' }
        @{ Type = 'Possible SQL injection (direct query)'; Pattern = '(mysql_query\s*\(|mysqli_query\s*\(|->query\s*\()' }
        @{ Type = 'Unescaped output'; Pattern = 'echo\s+["""]<[^>]+["""]\s*\.\s*\$' }
        @{ Type = 'file_get_contents with user input'; Pattern = 'file_get_contents\s*\(\s*\$' }
        @{ Type = 'include/require with variable'; Pattern = '(include|require)(_once)?\s*\(?\s*\$' }
        @{ Type = 'header() injection'; Pattern = 'header\s*\(\s*\$' }
        @{ Type = 'unserialize()'; Pattern = '\bunserialize\s*\(' }
        @{ Type = 'extract() usage'; Pattern = '\bextract\s*\(' }
        @{ Type = 'parse_str() without second arg'; Pattern = 'parse_str\s*\(\s*\$' }
    )

    $issuesByPattern = @()
    $phpFiles = Get-PhpFiles
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            $rel = Normalize-RelPath $f.FullName
            foreach ($p in $patterns) {
                if ($content -match $p.Pattern) {
                    $issuesByPattern += [pscustomobject]@{ File = $rel; PatternType = $p.Type }
                    Add-Issue -Type 'SECURITY_HEURISTIC' -Severity 'Medium' -File $rel -Message "Heuristic match: $($p.Type)"
                }
            }
        } catch {}
    }

    if ($issuesByPattern.Count -gt 0) {
        $md += ''
        $md += "## Potential Security Issues ($($issuesByPattern.Count) matches)"
        $md += ''
        $md += '| File | Issue Type |'
        $md += '|---|---|'
        foreach ($it in $issuesByPattern) { $md += "| $($it.File) | $($it.PatternType) |" }
    } else {
        $md += ''
        $md += '## Potential Security Issues'
        $md += ''
        $md += '_No heuristic matches detected (best-effort). This does not guarantee the code is secure._'
    }

    # .env exposure
    $envFile = Join-Path $script:ProjectRoot '.env'
    $md += ''
    $md += '## .env Exposure'
    $md += ''
    $md += "- .env file present: $(if(Test-Path $envFile){'Yes (WARNING: ensure it is excluded from version control)'}else{'No'})"

    # Check .env in .gitignore
    $gitignore = Join-Path $script:ProjectRoot '.gitignore'
    $envInGitignore = $false
    if (Test-Path $gitignore) {
        $giContent = Get-Content $gitignore -ErrorAction SilentlyContinue
        if ($giContent -match '^\.env$') { $envInGitignore = $true }
    }
    $md += "- .env in .gitignore: $(if($envInGitignore){'Yes'}else{'No (WARNING: .env may be committed!)'})"
    if (-not $envInGitignore) {
        Add-Issue -Type 'ENV_EXPOSURE' -Severity 'Critical' -File '.env' -Message '.env file not in .gitignore - risk of credential exposure'
    }

    # CSRF check
    $csrfFound = $false
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)csrf|csrf_token|csrf_field|csrf_meta') { $csrfFound = $true; break }
        } catch {}
    }
    $md += ''
    $md += '## CSRF Protection'
    $md += ''
    $md += "- CSRF implementation found: $(if($csrfFound){'Yes'}else{'No (WARNING: forms may be vulnerable to CSRF attacks)'})"
    if (-not $csrfFound) {
        Add-Issue -Type 'CSRF_MISSING' -Severity 'High' -File 'global' -Message 'No CSRF protection detected in PHP files'
    }

    # Session security
    $sessionFound = $false
    $sessionConfig = $false
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)session_start|session_id|$_SESSION') { $sessionFound = $true }
            if ($content -match '(?i)session_set_cookie_params|session_regenerate_id|session_destroy') { $sessionConfig = $true }
        } catch {}
    }
    $md += ''
    $md += '## Session Security'
    $md += ''
    $md += "- Session usage found: $(if($sessionFound){'Yes'}else{'No'})"
    $md += "- Session security configuration: $(if($sessionConfig){'Yes'}else{'No (WARNING: session fixation, hijacking risks)'})"

    # Password hashing
    $passwordHashing = $false
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)password_hash|password_verify|password_bcrypt|password_default') { $passwordHashing = $true; break }
        } catch {}
    }
    $md += ''
    $md += '## Password Hashing'
    $md += ''
    $md += "- Modern password hashing (bcrypt/argon2): $(if($passwordHashing){'Yes'}else{'No (WARNING: passwords may be stored insecurely)'})"
    if (-not $passwordHashing) {
        Add-Issue -Type 'PASSWORD_HASHING' -Severity 'High' -File 'global' -Message 'No modern password hashing (bcrypt/argon2) detected'
    }

    # OTP
    $otpFound = $false
    if (Test-Path (Join-Path $script:ProjectRoot 'helpers\otp.php')) { $otpFound = $true }
    $md += "- OTP implementation: $(if($otpFound){'Yes'}else{'No'})"

    # Rate Limiting
    $rateLimitFound = $false
    if (Test-Path (Join-Path $script:ProjectRoot 'helpers\rateLimiter.php')) { $rateLimitFound = $true }
    $md += "- Rate limiting: $(if($rateLimitFound){'Yes'}else{'No (WARNING: brute force attacks possible)'})"
    if (-not $rateLimitFound) {
        Add-Issue -Type 'RATE_LIMITING' -Severity 'Medium' -File 'global' -Message 'No rate limiting detected'
    }

    # Security Headers
    $md += ''
    $md += '## Security Headers'
    $md += ''
    $md += '| Header | Status |'
    $md += '|---|---|'
    $headersToCheck = @('X-Frame-Options','X-Content-Type-Options','X-XSS-Protection','Strict-Transport-Security','Content-Security-Policy','Referrer-Policy','Permissions-Policy')
    $htaccessPath = Join-Path $script:ProjectRoot '.htaccess'
    $htaccessContent = ''
    if (Test-Path $htaccessPath) { $htaccessContent = Get-Content $htaccessPath -Raw -ErrorAction SilentlyContinue }
    foreach ($hdr in $headersToCheck) {
        $found = $false
        if ($htaccessContent -match [regex]::Escape($hdr)) { $found = $true }
        if (-not $found) {
            foreach ($f in $phpFiles) {
                try {
                    $content = Get-Content $f.FullName -Raw -ErrorAction Stop
                    if ($content -match [regex]::Escape($hdr)) { $found = $true; break }
                } catch {}
            }
        }
        $md += "| $hdr | $(if($found){'Present'}else{'Missing'}) |"
        if (-not $found) {
            Add-Issue -Type 'SECURITY_HEADER' -Severity 'Medium' -File '.htaccess' -Message "Missing security header: $hdr"
        }
    }

    # Hardcoded credentials
    $credPatterns = @(
        @{ Pattern = '(?i)password\s*=\s*["""][^"""]+["""]'; Type = 'Hardcoded password' }
        @{ Pattern = '(?i)secret\s*=\s*["""][^"""]+["""]'; Type = 'Hardcoded secret' }
        @{ Pattern = '(?i)api_key\s*=\s*["""][^"""]+["""]'; Type = 'Hardcoded API key' }
        @{ Pattern = '(?i)db_password\s*=\s*["""][^"""]+["""]'; Type = 'Hardcoded DB password' }
    )
    $credIssues = @()
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            $rel = Normalize-RelPath $f.FullName
            foreach ($cp in $credPatterns) {
                if ($content -match $cp.Pattern) {
                    $credIssues += [pscustomobject]@{ File = $rel; Type = $cp.Type }
                    Add-Issue -Type 'HARDCODED_CREDENTIAL' -Severity 'Critical' -File $rel -Message "Possible $($cp.Type) detected"
                }
            }
        } catch {}
    }
    if ($credIssues.Count -gt 0) {
        $md += ''
        $md += '## Hardcoded Credentials (WARNING)'
        $md += ''
        $md += '| File | Type |'
        $md += '|---|---|'
        foreach ($ci in $credIssues) { $md += "| $($ci.File) | $($ci.Type) |" }
    }

    # Upload security
    $uploadDir = Join-Path $script:ProjectRoot 'public\uploads'
    $md += ''
    $md += '## Upload Security'
    $md += ''
    $md += "- Upload directory: $(if(Test-Path $uploadDir){'Present'}else{'Not found'})"
    if (Test-Path $uploadDir) {
        $uploadFiles = @(Get-ChildItem $uploadDir -Recurse -File -ErrorAction SilentlyContinue)
        $md += "- Files in uploads: $($uploadFiles.Count)"
        $phpInUploads = @($uploadFiles | Where-Object { $_.Extension -eq '.php' })
        $md += "- PHP files in uploads: $($phpInUploads.Count) $(if($phpInUploads.Count -gt 0){'(WARNING: executable files in upload directory!)'})"
        if ($phpInUploads.Count -gt 0) {
            Add-Issue -Type 'UPLOAD_SECURITY' -Severity 'Critical' -File 'public/uploads' -Message "PHP files found in uploads directory ($($phpInUploads.Count) files) - remote code execution risk"
        }
    }

    # Directory traversal check
    $md += ''
    $md += '## Directory Traversal'
    $md += ''
    $md += '- Checking for path traversal patterns in PHP files...'
    $traversalCount = 0
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)\.\.\/|\.\.\\|basename\s*\(|realpath\s*\(') { $traversalCount++ }
        } catch {}
    }
    $md += "- Files with path manipulation: $traversalCount"

    $md += ''
    $md += '## Recommendations'
    $md += '1. Implement CSRF tokens on all forms'
    $md += '2. Use prepared statements for all database queries'
    $md += '3. Add Content-Security-Policy header'
    $md += '4. Ensure .env is in .gitignore'
    $md += '5. Use password_hash()/password_verify() for all password operations'
    $md += '6. Implement rate limiting on login/OTP endpoints'
    $md += '7. Remove any hardcoded credentials from source code'
    $md += '8. Prevent PHP file execution in uploads directory'
    $md += '9. Add security headers via .htaccess or PHP'
    $md += '10. Validate and sanitize all user inputs'

    Save-Markdown (OutPath 'security_audit.md') ($md -join "`n")
    Save-Markdown (OutPath 'security_issues.md') ($md -join "`n")

    Write-Log 'Security analysis complete' 'INFO'
}

# ============================================================
# PHASE 6: ROUTE ANALYSIS
# ============================================================
function Invoke-Routes {
    Write-Color 'Phase 6 - Routes' -Color Magenta 'HEADER'
    Write-Log 'Starting route analysis' 'INFO'

    $publicDir = Join-Path $script:ProjectRoot 'public'
    $pages = @(); if (Test-Path $publicDir) { $pages = @(Get-ChildItem $publicDir -Recurse -File -Filter '*.php' -ErrorAction SilentlyContinue) }

    $publicPages = @($pages | Where-Object { $_.FullName -notmatch '\\admin\\' -and $_.FullName -notmatch '\\client\\' -and $_.FullName -notmatch '\\auth\\' })
    $adminPages  = @($pages | Where-Object { $_.FullName -match '\\admin\\' })
    $clientPages = @($pages | Where-Object { $_.FullName -match '\\client\\' })
    $authPages   = @($pages | Where-Object { $_.FullName -match '\\auth\\' })

    # Route files
    $routeDir = Join-Path $script:ProjectRoot 'routes'
    $routeFiles = @(); if (Test-Path $routeDir) { $routeFiles = @(Get-ChildItem $routeDir -File -Filter '*.php' -ErrorAction SilentlyContinue) }

    # AJAX endpoints
    $ajaxEndpoints = @()
    foreach ($f in $pages) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)ajax|xmlhttprequest|fetch\s*\(|\.ajax\s*\(') {
                $ajaxEndpoints += Normalize-RelPath $f.FullName
            }
        } catch {}
    }

    # Forms
    $formPages = @()
    foreach ($f in $pages) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)<form\s') {
                $formPages += Normalize-RelPath $f.FullName
            }
        } catch {}
    }

    $md = @()
    $md += '# Route / Page Discovery'
    $md += ''
    $md += '## Summary'
    $md += ''
    $md += '| Category | Count |'
    $md += '|---|---|'
    $md += "| Total PHP Pages | $($pages.Count) |"
    $md += "| Public Pages | $($publicPages.Count) |"
    $md += "| Admin Pages | $($adminPages.Count) |"
    $md += "| Client Pages | $($clientPages.Count) |"
    $md += "| Auth Pages | $($authPages.Count) |"
    $md += "| Route Files | $($routeFiles.Count) |"
    $md += "| AJAX Endpoints | $($ajaxEndpoints.Count) |"
    $md += "| Pages with Forms | $($formPages.Count) |"

    $md += ''
    $md += '## Public Pages'
    $md += ''
    $md += '| URL | File |'
    $md += '|---|---|'
    foreach ($p in $publicPages) {
        $rel = Normalize-RelPath $p.FullName
        $url = '/' + (($rel -replace '^public[\\/]','') -replace '\\','/' -replace '\.php$','')
        $md += "| $url | $rel |"
    }

    if ($adminPages.Count -gt 0) {
        $md += ''
        $md += '## Admin Pages'
        $md += ''
        $md += '| URL | File |'
        $md += '|---|---|'
        foreach ($p in $adminPages) {
            $rel = Normalize-RelPath $p.FullName
            $url = '/' + (($rel -replace '^public[\\/]','') -replace '\\','/' -replace '\.php$','')
            $md += "| $url | $rel |"
        }
    }

    if ($clientPages.Count -gt 0) {
        $md += ''
        $md += '## Client Pages'
        $md += ''
        $md += '| URL | File |'
        $md += '|---|---|'
        foreach ($p in $clientPages) {
            $rel = Normalize-RelPath $p.FullName
            $url = '/' + (($rel -replace '^public[\\/]','') -replace '\\','/' -replace '\.php$','')
            $md += "| $url | $rel |"
        }
    }

    if ($authPages.Count -gt 0) {
        $md += ''
        $md += '## Auth Pages'
        $md += ''
        $md += '| URL | File |'
        $md += '|---|---|'
        foreach ($p in $authPages) {
            $rel = Normalize-RelPath $p.FullName
            $url = '/' + (($rel -replace '^public[\\/]','') -replace '\\','/' -replace '\.php$','')
            $md += "| $url | $rel |"
        }
    }

    if ($routeFiles.Count -gt 0) {
        $md += ''
        $md += '## Route Definition Files'
        $md += ''
        $md += '| File |'
        $md += '|---|'
        foreach ($rf in $routeFiles) { $md += "| $(Normalize-RelPath $rf.FullName) |" }
    }

    if ($ajaxEndpoints.Count -gt 0) {
        $md += ''
        $md += '## AJAX Endpoints'
        $md += ''
        $md += '| File |'
        $md += '|---|'
        foreach ($ae in $ajaxEndpoints) { $md += "| $ae |" }
    }

    if ($formPages.Count -gt 0) {
        $md += ''
        $md += '## Pages with Forms'
        $md += ''
        $md += '| File |'
        $md += '|---|'
        foreach ($fp in $formPages) { $md += "| $fp |" }
    }

    # Check for 404 page
    $has404 = $false
    $has500 = $false
    foreach ($p in $pages) {
        $rel = Normalize-RelPath $p.FullName
        if ($rel -match '404') { $has404 = $true }
        if ($rel -match '500|error') { $has500 = $true }
    }
    $md += ''
    $md += '## Error Pages'
    $md += ''
    $md += "- 404 page: $(if($has404){'Found'}else{'Not found'})"
    $md += "- 500/Error page: $(if($has500){'Found'}else{'Not found'})"

    $md += ''
    $md += '## Recommendations'
    $md += '1. Ensure all routes have proper access controls'
    $md += '2. Add custom 404 and 500 error pages'
    $md += '3. Implement proper URL routing (consider using a framework router)'
    $md += '4. Add CSRF protection to all form submissions'
    $md += '5. Validate and sanitize all AJAX endpoint inputs'

    Save-Markdown (OutPath 'routes_discovery.md') ($md -join "`n")

    Write-Log 'Route analysis complete' 'INFO'
}

# ============================================================
# PHASE 7: ASSET ANALYSIS
# ============================================================
function Invoke-Assets {
    Write-Color 'Phase 7 - Assets' -Color Magenta 'HEADER'
    Write-Log 'Starting asset analysis' 'INFO'

    $assets = Join-Path $script:ProjectRoot 'public\assets'
    $md = @()
    $md += '# Asset Analysis'
    $md += ''

    if (-not (Test-Path $assets)) {
        $md += '_public/assets directory missing._'
        Save-Markdown (OutPath 'assets_analysis.md') ($md -join "`n")
        return
    }

    $imgs = @(Get-ChildItem $assets -Recurse -File -Include '*.png','*.jpg','*.jpeg','*.gif','*.svg','*.webp','*.ico' -ErrorAction SilentlyContinue)
    $cssFiles = @(Get-ChildItem $assets -Recurse -File -Filter '*.css' -ErrorAction SilentlyContinue)
    $jsFiles = @(Get-ChildItem $assets -Recurse -File -Filter '*.js' -ErrorAction SilentlyContinue)
    $fontFiles = @(Get-ChildItem $assets -Recurse -File -Include '*.woff','*.woff2','*.ttf','*.eot','*.otf' -ErrorAction SilentlyContinue)

    $md += '## Asset Inventory'
    $md += ''
    $md += '| Asset Type | Count | Total Size (KB) |'
    $md += '|---|---:|---:|'
    $md += "| Images | $($imgs.Count) | $([math]::Round((($imgs|Measure-Object Length -Sum).Sum)/1KB,1)) |"
    $md += "| CSS | $($cssFiles.Count) | $([math]::Round((($cssFiles|Measure-Object Length -Sum).Sum)/1KB,1)) |"
    $md += "| JavaScript | $($jsFiles.Count) | $([math]::Round((($jsFiles|Measure-Object Length -Sum).Sum)/1KB,1)) |"
    $md += "| Fonts | $($fontFiles.Count) | $([math]::Round((($fontFiles|Measure-Object Length -Sum).Sum)/1KB,1)) |"

    # Large assets
    $allAssets = $imgs + $cssFiles + $jsFiles + $fontFiles
    $large = @($allAssets | Where-Object { $_.Length -gt 500KB })
    if ($large.Count -gt 0) {
        $md += ''
        $md += '## Large Assets (>500KB)'
        $md += ''
        $md += '| File | Size (KB) | Type |'
        $md += '|---|---:|---|'
        foreach ($l in $large) {
            $rel = Normalize-RelPath $l.FullName
            $kb = [math]::Round($l.Length/1KB,1)
            $type = $l.Extension
            $md += "| $rel | $kb | $type |"
            Add-Issue -Type 'LARGE_ASSET' -Severity 'Low' -File $rel -Message "Large asset: ${kb}KB"
        }
    }

    # Missing favicon
    $favicon = Join-Path $script:ProjectRoot 'public\favicon.ico'
    $md += ''
    $md += '## Missing Assets Check'
    $md += ''
    $md += "| Asset | Status |"
    $md += '|---|---|'
    $md += "| favicon.ico | $(if(Test-Path $favicon){'Present'}else{'Missing'}) |"
    if (-not (Test-Path $favicon)) {
        Add-Issue -Type 'MISSING_FAVICON' -Severity 'Low' -File 'public/favicon.ico' -Message 'Missing favicon.ico'
    }

    # Check for broken CSS/JS references in PHP files
    $md += ''
    $md += '## Asset Reference Check (Best-Effort)'
    $md += ''
    $phpFiles = Get-PhpFiles
    $missingRefs = @()
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            $refs = [regex]::Matches($content, '(?:src|href)\s*=\s*["""]([^"""]+\.(?:css|js|png|jpg|jpeg|gif|svg|webp|ico|woff|woff2|ttf|eot|otf))["""]')
            foreach ($r in $refs) {
                $refPath = $r.Groups[1].Value
                # Resolve relative to project root
                $resolved = Join-Path $script:ProjectRoot 'public' $refPath
                if (-not (Test-Path $resolved)) {
                    $missingRefs += [pscustomobject]@{ File = Normalize-RelPath $f.FullName; Reference = $refPath }
                }
            }
        } catch {}
    }
    if ($missingRefs.Count -gt 0) {
        $md += ''
        $md += '### Potentially Broken References'
        $md += ''
        $md += '| Source File | Missing Reference |'
        $md += '|---|---|'
        foreach ($mr in $missingRefs) { $md += "| $($mr.File) | $($mr.Reference) |" }
    } else {
        $md += ''
        $md += '_No broken asset references detected (best-effort)._'
    }

    # Duplicate assets
    $md += ''
    $md += '## Duplicate Assets'
    $md += ''
    $assetNames = @{}
    foreach ($a in $allAssets) {
        $n = $a.Name.ToLower()
        if (-not $assetNames.ContainsKey($n)) { $assetNames[$n] = @() }
        $assetNames[$n] += Normalize-RelPath $a.FullName
    }
    $dups = $assetNames.GetEnumerator() | Where-Object { $_.Value.Count -gt 1 }
    if ($dups.Count -gt 0) {
        $md += '| Name | Files |'
        $md += '|---|---|'
        foreach ($d in $dups) { $md += "| $($d.Key) | $([string]::Join('; ', $d.Value)) |" }
    } else {
        $md += '_No duplicate assets found._'
    }

    $md += ''
    $md += '## Recommendations'
    $md += '1. Optimize large images (compress, use WebP format)'
    $md += '2. Minify CSS and JavaScript files'
    $md += '3. Add favicon.ico'
    $md += '4. Remove unused or duplicate assets'
    $md += '5. Implement lazy loading for images'
    $md += '6. Use CDN for common libraries (Bootstrap, jQuery)'

    Save-Markdown (OutPath 'assets_analysis.md') ($md -join "`n")

    Write-Log 'Asset analysis complete' 'INFO'
}

# ============================================================
# PHASE 8: PERFORMANCE ANALYSIS
# ============================================================
function Invoke-Performance {
    Write-Color 'Phase 8 - Performance' -Color Magenta 'HEADER'
    Write-Log 'Starting performance analysis' 'INFO'

    $phpFiles = Get-PhpFiles
    $allFiles = Get-AllFiles

    $largePhp = @($phpFiles | Where-Object { $_.Length -gt 100KB })
    $largeImages = @($allFiles | Where-Object { $_.Extension -match '\.(png|jpg|jpeg|gif)$' -and $_.Length -gt 200KB })
    $largeJs = @($allFiles | Where-Object { $_.Extension -eq '.js' -and $_.Length -gt 100KB })
    $largeCss = @($allFiles | Where-Object { $_.Extension -eq '.css' -and $_.Length -gt 50KB })

    $md = @()
    $md += '# Performance Analysis (Static Heuristics)'
    $md += ''
    $md += '## Summary'
    $md += ''
    $md += '| Metric | Value |'
    $md += '|---|---|'
    $md += "| Large PHP Files (>100KB) | $($largePhp.Count) |"
    $md += "| Large Images (>200KB) | $($largeImages.Count) |"
    $md += "| Large JS Files (>100KB) | $($largeJs.Count) |"
    $md += "| Large CSS Files (>50KB) | $($largeCss.Count) |"

    if ($largePhp.Count -gt 0) {
        $md += ''
        $md += '## Large PHP Files'
        $md += ''
        $md += '| File | Size (KB) |'
        $md += '|---|---:|'
        foreach ($f in $largePhp) { $rel = Normalize-RelPath $f.FullName; $kb = [math]::Round($f.Length/1KB,1); $md += "| $rel | $kb |" }
    }

    if ($largeImages.Count -gt 0) {
        $md += ''
        $md += '## Large Images'
        $md += ''
        $md += '| File | Size (KB) |'
        $md += '|---|---:|'
        foreach ($f in $largeImages) { $rel = Normalize-RelPath $f.FullName; $kb = [math]::Round($f.Length/1KB,1); $md += "| $rel | $kb |" }
    }

    if ($largeJs.Count -gt 0) {
        $md += ''
        $md += '## Large JavaScript Files'
        $md += ''
        $md += '| File | Size (KB) |'
        $md += '|---|---:|'
        foreach ($f in $largeJs) { $rel = Normalize-RelPath $f.FullName; $kb = [math]::Round($f.Length/1KB,1); $md += "| $rel | $kb |" }
    }

    if ($largeCss.Count -gt 0) {
        $md += ''
        $md += '## Large CSS Files'
        $md += ''
        $md += '| File | Size (KB) |'
        $md += '|---|---:|'
        foreach ($f in $largeCss) { $rel = Normalize-RelPath $f.FullName; $kb = [math]::Round($f.Length/1KB,1); $md += "| $rel | $kb |" }
    }

    # Slow SQL patterns
    $sqlFiles = @()
    try { $sqlFiles = Get-ChildItem -Path $script:ProjectRoot -Recurse -File -Filter '*.sql' -ErrorAction SilentlyContinue | Where-Object { $_.FullName -notmatch 'node_modules|audit-report' } } catch {}
    $slowSqlPatterns = @('SELECT\s+\*\s+FROM','JOIN\s+\w+\s+ON\s+\w+\s*=\s*\w+','LEFT\s+JOIN','RIGHT\s+JOIN','SUBQUERY','GROUP\s+BY\s+\w+','ORDER\s+BY\s+RAND\(\)')
    $slowSqlCount = 0
    foreach ($sf in $sqlFiles) {
        try {
            $content = Get-Content $sf.FullName -Raw -ErrorAction Stop
            foreach ($sp in $slowSqlPatterns) {
                if ($content -match $sp) { $slowSqlCount++ }
            }
        } catch {}
    }
    $md += ''
    $md += '## SQL Performance'
    $md += ''
    $md += "- SQL files scanned: $($sqlFiles.Count)"
    $md += "- Potential slow queries detected: $slowSqlCount"

    # Missing indexes check
    $md += ''
    $md += '## Index Analysis'
    $md += ''
    $md += '- Checking for tables without indexes (best-effort)...'
    $tablesWithoutIndexes = 0
    foreach ($sf in $sqlFiles) {
        try {
            $content = Get-Content $sf.FullName -Raw -ErrorAction Stop
            $tableDefs = [regex]::Matches($content, '(?i)create\s+table\s+(?:if\s+not\s+exists\s+)?[`"\[]?([A-Za-z0-9_]+)[`"\]]?\s*\(([^)]+)\)', [System.Text.RegularExpressions.RegexOptions]::Singleline)
            foreach ($td in $tableDefs) {
                $block = $td.Groups[2].Value
                if ($block -notmatch '(?i)index|key|primary\s+key|unique') { $tablesWithoutIndexes++ }
            }
        } catch {}
    }
    $md += "- Tables potentially missing indexes: $tablesWithoutIndexes"

    # OPcache
    $md += ''
    $md += '## Caching & Optimization'
    $md += ''
    $md += '| Feature | Status |'
    $md += '|---|---|'
    $opcacheFound = $false
    if (Has-PhpCli) {
        try {
            $phpInfo = php -i 2>&1 | Out-String
            if ($phpInfo -match 'opcache\.enable\s+=>\s+On') { $opcacheFound = $true }
        } catch {}
    }
    $md += "| OPcache | $(if($opcacheFound){'Enabled'}else{'Not detected (recommended)'}) |"

    # Check for .htaccess caching
    $htaccessPath = Join-Path $script:ProjectRoot '.htaccess'
    $cacheHeaders = $false
    if (Test-Path $htaccessPath) {
        $htContent = Get-Content $htaccessPath -Raw -ErrorAction SilentlyContinue
        if ($htContent -match '(?i)expires|Cache-Control|mod_expires|Header\s+set\s+Cache') { $cacheHeaders = $true }
    }
    $md += "| Cache Headers | $(if($cacheHeaders){'Configured'}else{'Not configured (recommended)'}) |"

    # Compression
    $compression = $false
    if (Test-Path $htaccessPath) {
        $htContent = Get-Content $htaccessPath -Raw -ErrorAction SilentlyContinue
        if ($htContent -match '(?i)deflate|gzip|mod_deflate|mod_brotli') { $compression = $true }
    }
    $md += "| Compression (gzip/deflate) | $(if($compression){'Configured'}else{'Not configured (recommended)'}) |"

    $md += ''
    $md += '## Recommendations'
    $md += '1. Enable OPcache for PHP performance'
    $md += '2. Add caching headers for static assets (Cache-Control, Expires)'
    $md += '3. Enable gzip/deflate compression'
    $md += '4. Optimize and compress large images'
    $md += '5. Minify CSS and JavaScript'
    $md += '6. Add missing database indexes'
    $md += '7. Optimize slow SQL queries (avoid SELECT *, add proper JOINs)'
    $md += '8. Implement lazy loading for images and content'
    $md += '9. Use CDN for third-party libraries'
    $md += '10. Consider implementing Redis/Memcached for caching'

    Save-Markdown (OutPath 'performance_analysis.md') ($md -join "`n")

    Write-Log 'Performance analysis complete' 'INFO'
}

# ============================================================
# PHASE 9: SEO ANALYSIS
# ============================================================
function Invoke-Seo {
    Write-Color 'Phase 9 - SEO' -Color Magenta 'HEADER'
    Write-Log 'Starting SEO analysis' 'INFO'

    $publicDir = Join-Path $script:ProjectRoot 'public'
    $pages = @(); if (Test-Path $publicDir) { $pages = @(Get-ChildItem $publicDir -Recurse -File -Filter '*.php' -ErrorAction SilentlyContinue) }

    $robots = Join-Path $publicDir 'robots.txt'
    $sitemap = Join-Path $publicDir 'sitemap.xml'

    $md = @()
    $md += '# SEO Analysis'
    $md += ''
    $md += '## Basic SEO Checks'
    $md += ''
    $md += '| Check | Status |'
    $md += '|---|---|'
    $md += "| robots.txt | $(if(Test-Path $robots){'Present'}else{'Missing'}) |"
    $md += "| sitemap.xml | $(if(Test-Path $sitemap){'Present'}else{'Missing'}) |"

    if (Test-Path $robots) {
        $robotsContent = Get-Content $robots -Raw -ErrorAction SilentlyContinue
        $md += ''
        $md += '### robots.txt Content'
        $md += '```'
        $md += $robotsContent
        $md += '```'
    }

    if (Test-Path $sitemap) {
        $sitemapContent = Get-Content $sitemap -Raw -ErrorAction SilentlyContinue
        $md += ''
        $md += '### sitemap.xml Content'
        $md += '```xml'
        $md += $sitemapContent
        $md += '```'
    }

    # Page-level SEO
    $titleCount = 0
    $descCount = 0
    $canonicalCount = 0
    $ogCount = 0
    $h1Count = 0
    $h2Count = 0
    $structuredDataCount = 0
    $scanned = 0
    $pagesWithoutTitle = @()
    $pagesWithoutDesc = @()

    foreach ($p in $pages) {
        try {
            $c = Get-Content $p.FullName -Raw -ErrorAction Stop
            $scanned++
            $rel = Normalize-RelPath $p.FullName

            if ($c -match '(?i)<title>') { $titleCount++ } else { $pagesWithoutTitle += $rel }
            if ($c -match '(?i)meta\s+name=["""]description') { $descCount++ } else { $pagesWithoutDesc += $rel }
            if ($c -match '(?i)rel=["""]canonical["""]') { $canonicalCount++ }
            if ($c -match '(?i)og:title|og:description|og:image|og:url') { $ogCount++ }
            if ($c -match '(?i)<h1\b') { $h1Count++ }
            if ($c -match '(?i)<h2\b') { $h2Count++ }
            if ($c -match '(?i)application/ld\+json|itemscope|itemtype|itemprop') { $structuredDataCount++ }
        } catch {}
    }

    $md += ''
    $md += '## Page-Level SEO'
    $md += ''
    $md += '| Metric | Count | Percentage |'
    $md += '|---|---:|---:|'
    $md += "| Pages Scanned | $scanned | 100% |"
    $md += "| With Title Tag | $titleCount | $(if($scanned -gt 0){[math]::Round($titleCount/$scanned*100,1)}else{0})% |"
    $md += "| With Meta Description | $descCount | $(if($scanned -gt 0){[math]::Round($descCount/$scanned*100,1)}else{0})% |"
    $md += "| With Canonical URL | $canonicalCount | $(if($scanned -gt 0){[math]::Round($canonicalCount/$scanned*100,1)}else{0})% |"
    $md += "| With OpenGraph Tags | $ogCount | $(if($scanned -gt 0){[math]::Round($ogCount/$scanned*100,1)}else{0})% |"
    $md += "| With H1 Heading | $h1Count | $(if($scanned -gt 0){[math]::Round($h1Count/$scanned*100,1)}else{0})% |"
    $md += "| With H2 Heading | $h2Count | $(if($scanned -gt 0){[math]::Round($h2Count/$scanned*100,1)}else{0})% |"
    $md += "| With Structured Data | $structuredDataCount | $(if($scanned -gt 0){[math]::Round($structuredDataCount/$scanned*100,1)}else{0})% |"

    if ($pagesWithoutTitle.Count -gt 0) {
        $md += ''
        $md += '### Pages Missing Title Tags'
        $md += ''
        foreach ($pw in $pagesWithoutTitle) { $md += "- $pw" }
    }

    if ($pagesWithoutDesc.Count -gt 0) {
        $md += ''
        $md += '### Pages Missing Meta Description'
        $md += ''
        foreach ($pw in $pagesWithoutDesc) { $md += "- $pw" }
    }

    # Heading hierarchy check
    $md += ''
    $md += '## Heading Hierarchy'
    $md += ''
    $md += '- Checking for proper H1->H2->H3 hierarchy...'
    $hierarchyIssues = 0
    foreach ($p in $pages) {
        try {
            $lines = Get-Content $p.FullName -ErrorAction Stop
            $prevLevel = 0
            foreach ($line in $lines) {
                if ($line -match '(?i)<h([1-6])\b') {
                    $level = [int]$matches[1]
                    if ($level -gt $prevLevel + 1) { $hierarchyIssues++ }
                    $prevLevel = $level
                }
            }
        } catch {}
    }
    $md += "- Heading hierarchy issues: $hierarchyIssues"

    $md += ''
    $md += '## Recommendations'
    $md += '1. Add unique title tags to all pages'
    $md += '2. Add meta descriptions to all pages'
    $md += '3. Implement canonical URLs to prevent duplicate content'
    $md += '4. Add OpenGraph tags for social media sharing'
    $md += '5. Ensure proper heading hierarchy (H1 -> H2 -> H3)'
    $md += '6. Add structured data (JSON-LD) for rich snippets'
    $md += '7. Create and submit sitemap.xml to search engines'
    $md += '8. Optimize robots.txt for proper crawling'
    $md += '9. Add alt text to all images'
    $md += '10. Ensure mobile-friendly responsive design'

    Save-Markdown (OutPath 'seo_analysis.md') ($md -join "`n")

    Write-Log 'SEO analysis complete' 'INFO'
}

# ============================================================
# PHASE 10: UI ANALYSIS
# ============================================================
function Invoke-Ui {
    Write-Color 'Phase 10 - UI' -Color Magenta 'HEADER'
    Write-Log 'Starting UI analysis' 'INFO'

    $assets = Join-Path $script:ProjectRoot 'public\assets'
    $phpFiles = Get-PhpFiles

    $bootstrap = @(); $jquery = @(); $fontawesome = @()
    if (Test-Path $assets) {
        $bootstrap   = @(Get-ChildItem $assets -Recurse -File -Include 'bootstrap*' -ErrorAction SilentlyContinue)
        $jquery      = @(Get-ChildItem $assets -Recurse -File -Include 'jquery*' -ErrorAction SilentlyContinue)
        $fontawesome = @(Get-ChildItem $assets -Recurse -File -Include 'font-awesome*','fontawesome*' -ErrorAction SilentlyContinue)
    }

    $md = @()
    $md += '# UI Analysis'
    $md += ''
    $md += '## Framework Detection'
    $md += ''
    $md += '| Framework | Detected | Files |'
    $md += '|---|---|---|'
    $md += "| Bootstrap | $(if($bootstrap.Count -gt 0){'Yes'}else{'No'}) | $($bootstrap.Count) |"
    $md += "| jQuery | $(if($jquery.Count -gt 0){'Yes'}else{'No'}) | $($jquery.Count) |"
    $md += "| Font Awesome | $(if($fontawesome.Count -gt 0){'Yes'}else{'No'}) | $($fontawesome.Count) |"

    # Check for responsive meta tag
    $responsiveCount = 0
    $navbarCount = 0
    $sidebarCount = 0
    $formCount = 0
    $dashboardCount = 0
    $accessibilityIssues = 0

    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            $rel = Normalize-RelPath $f.FullName

            if ($content -match '(?i)viewport|responsive|@media') { $responsiveCount++ }
            if ($content -match '(?i)navbar|nav\s*class|navigation') { $navbarCount++ }
            if ($content -match '(?i)sidebar|side-bar') { $sidebarCount++ }
            if ($content -match '(?i)<form\s') { $formCount++ }
            if ($content -match '(?i)dashboard|admin-panel') { $dashboardCount++ }
            if ($content -match '(?i)alt\s*=|aria-|role\s*=') { } else {
                if ($content -match '<img\s') { $accessibilityIssues++ }
            }
        } catch {}
    }

    $md += ''
    $md += '## UI Components'
    $md += ''
    $md += '| Component | Pages Using |'
    $md += '|---|---|'
    $md += "| Responsive Design | $responsiveCount |"
    $md += "| Navbar | $navbarCount |"
    $md += "| Sidebar | $sidebarCount |"
    $md += "| Forms | $formCount |"
    $md += "| Dashboard | $dashboardCount |"

    $md += ''
    $md += '## Accessibility'
    $md += ''
    $md += '- Checking for basic accessibility attributes...'
    $md += "- Pages with images missing alt text (potential issues): $accessibilityIssues"

    # Check for responsive CSS
    $cssFiles = @()
    if (Test-Path $assets) { $cssFiles = @(Get-ChildItem $assets -Recurse -File -Filter '*.css' -ErrorAction SilentlyContinue) }
    $responsiveCss = 0
    foreach ($cf in $cssFiles) {
        try {
            $content = Get-Content $cf.FullName -Raw -ErrorAction Stop
            if ($content -match '@media') { $responsiveCss++ }
        } catch {}
    }
    $md += "- CSS files with media queries: $responsiveCss / $($cssFiles.Count)"

    $md += ''
    $md += '## Recommendations'
    $md += '1. Ensure all pages have proper viewport meta tag for mobile responsiveness'
    $md += '2. Add alt text to all images for accessibility'
    $md += '3. Use semantic HTML elements (header, nav, main, footer)'
    $md += '4. Implement proper ARIA attributes for screen readers'
    $md += '5. Ensure color contrast meets WCAG standards'
    $md += '6. Add keyboard navigation support'
    $md += '7. Test on multiple screen sizes and browsers'
    $md += '8. Consider using a CSS framework like Bootstrap for consistency'

    Save-Markdown (OutPath 'ui_analysis.md') ($md -join "`n")

    Write-Log 'UI analysis complete' 'INFO'
}

# ============================================================
# PHASE 11: DEPLOYMENT ANALYSIS
# ============================================================
function Invoke-Deployment {
    Write-Color 'Phase 11 - Deployment' -Color Magenta 'HEADER'
    Write-Log 'Starting deployment analysis' 'INFO'

    $checks = @(
        @{ Name = '.htaccess'; Path = '.htaccess'; Description = 'Apache URL rewriting' }
        @{ Name = 'docker-compose.yml'; Path = 'docker-compose.yml'; Description = 'Container orchestration' }
        @{ Name = 'deploy.sh'; Path = 'deploy.sh'; Description = 'Deployment script' }
        @{ Name = 'DEPLOYMENT.md'; Path = 'DEPLOYMENT.md'; Description = 'Deployment documentation' }
        @{ Name = 'Dockerfile'; Path = 'Dockerfile'; Description = 'Docker build file' }
        @{ Name = '.env.example'; Path = '.env.example'; Description = 'Environment template' }
        @{ Name = 'README.md'; Path = 'README.md'; Description = 'Project documentation' }
        @{ Name = 'docker/php/php.ini'; Path = 'docker/php/php.ini'; Description = 'PHP configuration' }
        @{ Name = 'docker/apache/vhost.conf'; Path = 'docker/apache/vhost.conf'; Description = 'Apache virtual host' }
    )

    $md = @()
    $md += '# Deployment Analysis'
    $md += ''
    $md += '## Deployment Files'
    $md += ''
    $md += '| File | Status | Description |'
    $md += '|---|---|---|'
    foreach ($c in $checks) {
        $full = Join-Path $script:ProjectRoot $c.Path
        $status = if (Test-Path $full) { 'Present' } else { 'Missing' }
        $md += "| $($c.Name) | $status | $($c.Description) |"
        if ($status -eq 'Missing') {
            Add-Issue -Type 'DEPLOYMENT_FILE_MISSING' -Severity 'Medium' -File $c.Path -Message "Missing deployment file: $($c.Name)"
        }
    }

    # HTTPS check
    $md += ''
    $md += '## HTTPS / SSL'
    $md += ''
    $md += '- Checking for HTTPS configuration...'
    $httpsFound = $false
    $sslFound = $false
    $phpFiles = Get-PhpFiles
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)https://|force_ssl|redirect_to_https|HTTPS\s*===\s*["""]on["""]') { $httpsFound = $true }
            if ($content -match '(?i)ssl|certificate|openssl') { $sslFound = $true }
        } catch {}
    }
    $htaccessPath = Join-Path $script:ProjectRoot '.htaccess'
    if (Test-Path $htaccessPath) {
        $htContent = Get-Content $htaccessPath -Raw -ErrorAction SilentlyContinue
        if ($htContent -match '(?i)https|ssl|443') { $httpsFound = $true }
    }
    $md += "- HTTPS enforcement: $(if($httpsFound){'Configured'}else{'Not detected (recommended for production)'})"
    $md += "- SSL references: $(if($sslFound){'Found'}else{'Not detected'})"

    # Backups
    $md += ''
    $md += '## Backups'
    $md += ''
    $md += '- Checking for backup configuration...'
    $backupFound = $false
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)backup|dump|export|mysqldump|pg_dump') { $backupFound = $true; break }
        } catch {}
    }
    $md += "- Backup scripts/configuration: $(if($backupFound){'Found'}else{'Not detected (recommended)'})"

    # Cron / Scheduler
    $md += ''
    $md += '## Cron Jobs / Scheduler'
    $md += ''
    $cronFound = $false
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)cron|schedule|scheduler|task\s+scheduler') { $cronFound = $true; break }
        } catch {}
    }
    $md += "- Cron/scheduler configuration: $(if($cronFound){'Found'}else{'Not detected'})"

    # Logging
    $md += ''
    $md += '## Logging'
    $md += ''
    $logDir = Join-Path $script:ProjectRoot 'storage\logs'
    $logFound = if (Test-Path $logDir) { $true } else { $false }
    $md += "- Log directory: $(if($logFound){'Present'}else{'Not found'})"
    if ($logFound) {
        $logFiles = @(Get-ChildItem $logDir -File -ErrorAction SilentlyContinue)
        $md += "- Log files: $($logFiles.Count)"
    }
    $errorLogging = $false
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)error_log|log_message|monolog|logger->') { $errorLogging = $true; break }
        } catch {}
    }
    $md += "- PHP error logging: $(if($errorLogging){'Configured'}else{'Not detected'})"

    $md += ''
    $md += '## Recommendations'
    $md += '1. Set up automated backup system (database + files)'
    $md += '2. Configure cron jobs for scheduled tasks (email, cleanup, reports)'
    $md += '3. Enable HTTPS with valid SSL certificate'
    $md += '4. Set up centralized logging (e.g., ELK stack, Papertrail)'
    $md += '5. Create deployment documentation (DEPLOYMENT.md)'
    $md += '6. Implement CI/CD pipeline (GitHub Actions, Jenkins)'
    $md += '7. Configure monitoring and alerting'
    $md += '8. Set up staging environment for testing'
    $md += '9. Document rollback procedures'
    $md += '10. Perform regular security updates'

    Save-Markdown (OutPath 'deployment_analysis.md') ($md -join "`n")

    Write-Log 'Deployment analysis complete' 'INFO'
}

# ============================================================
# PHASE 12: ERROR REPORTS
# ============================================================
function Invoke-ErrorReports {
    Write-Color 'Phase 12 - Error Reports' -Color Magenta 'HEADER'
    Write-Log 'Generating error reports' 'INFO'

    $phpFiles = Get-PhpFiles

    # SQL Errors
    $sqlErrors = @()
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            $matches = [regex]::Matches($content, '(?i)(sql\s+error|database\s+error|mysql_error|mysqli_error|PDOException|query\s+failed)')
            if ($matches.Count -gt 0) {
                $sqlErrors += [pscustomobject]@{ File = Normalize-RelPath $f.FullName; Count = $matches.Count }
            }
        } catch {}
    }
    $md = @()
    $md += '# SQL Errors'
    $md += ''
    if ($sqlErrors.Count -gt 0) {
        $md += "Found $($sqlErrors.Count) files with SQL error handling."
        $md += ''
        $md += '| File | Occurrences |'
        $md += '|---|---|'
        foreach ($se in $sqlErrors) { $md += "| $($se.File) | $($se.Count) |" }
    } else {
        $md += '_No SQL error patterns detected._'
    }
    Save-Markdown (OutPath 'reports_sql-errors.md') ($md -join "`n")

    # JS Errors
    $jsErrors = @()
    $jsFiles = @()
    $assetsDir = Join-Path $script:ProjectRoot 'public\assets'
    if (Test-Path $assetsDir) { $jsFiles = @(Get-ChildItem $assetsDir -Recurse -File -Filter '*.js' -ErrorAction SilentlyContinue) }
    foreach ($jf in $jsFiles) {
        try {
            $content = Get-Content $jf.FullName -Raw -ErrorAction Stop
            if ($content -match '(?i)console\.error|throw\s+new\s+Error|try\s*\{|catch\s*\(') {
                $jsErrors += Normalize-RelPath $jf.FullName
            }
        } catch {}
    }
    $md = @()
    $md += '# JavaScript Errors'
    $md += ''
    $md += "JS files scanned: $($jsFiles.Count)"
    $md += "Files with error handling: $($jsErrors.Count)"
    if ($jsErrors.Count -gt 0) {
        $md += ''
        $md += '| File |'
        $md += '|---|'
        foreach ($je in $jsErrors) { $md += "| $je |" }
    }
    Save-Markdown (OutPath 'reports_js-errors.md') ($md -join "`n")

    # HTTP Errors
    $md = @()
    $md += '# HTTP Errors'
    $md += ''
    $md += '| Error Type | Status |'
    $md += '|---|---|'
    $has404 = $false
    $has500 = $false
    foreach ($f in $phpFiles) {
        $rel = Normalize-RelPath $f.FullName
        if ($rel -match '404') { $has404 = $true }
        if ($rel -match '500|error|exception') { $has500 = $true }
    }
    $md += "| 404 Not Found | $(if($has404){'Custom page found'}else{'No custom 404 page'}) |"
    $md += "| 500 Server Error | $(if($has500){'Custom page found'}else{'No custom 500 page'}) |"
    Save-Markdown (OutPath 'reports_http-errors.md') ($md -join "`n")

    # 404 Report
    $md = @()
    $md += '# 404 Not Found Report'
    $md += ''
    $md += "Custom 404 page: $(if($has404){'Found'}else{'Not found'})"
    if (-not $has404) {
        $md += ''
        $md += '**WARNING:** No custom 404 page detected. Users will see default server error pages.'
        Add-Issue -Type 'MISSING_404_PAGE' -Severity 'Medium' -File 'public/404.php' -Message 'No custom 404 error page found'
    }
    Save-Markdown (OutPath 'reports_404.md') ($md -join "`n")

    # 500 Report
    $md = @()
    $md += '# 500 Server Error Report'
    $md += ''
    $md += "Custom 500/error page: $(if($has500){'Found'}else{'Not found'})"
    if (-not $has500) {
        $md += ''
        $md += '**WARNING:** No custom error page detected. Internal server errors will show default error messages.'
        Add-Issue -Type 'MISSING_500_PAGE' -Severity 'Medium' -File 'public/500.php' -Message 'No custom 500 error page found'
    }
    Save-Markdown (OutPath 'reports_500.md') ($md -join "`n")

    # Exceptions
    $md = @()
    $md += '# Exception Handling Report'
    $md += ''
    $exceptionCount = 0
    foreach ($f in $phpFiles) {
        try {
            $content = Get-Content $f.FullName -Raw -ErrorAction Stop
            $matches = [regex]::Matches($content, '(?i)(throw\s+new\s+\w+Exception|try\s*\{|catch\s*\()')
            if ($matches.Count -gt 0) { $exceptionCount++ }
        } catch {}
    }
    $md += "Files with exception handling: $exceptionCount / $($phpFiles.Count)"
    $md += ''
    $md += '## Recommendations'
    $md += '1. Implement global exception handler'
    $md += '2. Add custom 404 and 500 error pages'
    $md += '3. Log all exceptions to a file'
    $md += '4. Use try-catch blocks for database operations'
    $md += '5. Display user-friendly error messages'
    Save-Markdown (OutPath 'reports_exceptions.md') ($md -join "`n")

    Write-Log 'Error reports generated' 'INFO'
}

# ============================================================
# PHASE 13: TODO GENERATOR
# ============================================================
function Invoke-TodoGenerator {
    Write-Color 'Phase 13 - TODO Generator' -Color Magenta 'HEADER'
    Write-Log 'Generating TODO lists' 'INFO'

    $bySev = @{
        Critical = @($script:Issues | Where-Object { $_.Severity -eq 'Critical' })
        High     = @($script:Issues | Where-Object { $_.Severity -eq 'High' })
        Medium   = @($script:Issues | Where-Object { $_.Severity -eq 'Medium' })
        Low      = @($script:Issues | Where-Object { $_.Severity -eq 'Low' })
    }

    function WriteTodo($sev, $file) {
        $items = $bySev[$sev]
        $md = @()
        $md += "# $sev Issues"
        $md += ''
        $md += "Total: $($items.Count)"
        $md += ''
        if ($items.Count -eq 0) {
            $md += '_None detected._'
            Save-Markdown (OutPath $file) ($md -join "`n")
            return
        }
        $md += '| # | Type | File | Issue |'
        $md += '|---|---|---|---|'
        $i = 1
        foreach ($it in $items) {
            $msg = ($it.Message -replace '\|','/')
            $md += "| $i | $($it.Type) | $($it.File) | $msg |"
            $i++
        }
        Save-Markdown (OutPath $file) ($md -join "`n")
    }

    WriteTodo 'Critical' 'todo_critical.md'
    WriteTodo 'High' 'todo_high.md'
    WriteTodo 'Medium' 'todo_medium.md'
    WriteTodo 'Low' 'todo_low.md'

    # Refactoring suggestions
    $md = @()
    $md += '# Refactoring Suggestions'
    $md += ''
    $md += '## Code Quality Improvements'
    $md += ''
    $dupFuncs = @($script:Issues | Where-Object { $_.Type -eq 'DUPLICATE_FUNCTION' })
    $dupClasses = @($script:Issues | Where-Object { $_.Type -eq 'DUPLICATE_CLASS' })
    $deprecated = @($script:Issues | Where-Object { $_.Type -eq 'DEPRECATED_FUNCTION' })
    $md += "1. Remove duplicate functions ($($dupFuncs.Count) found)"
    $md += "2. Remove duplicate classes ($($dupClasses.Count) found)"
    $md += "3. Replace deprecated functions ($($deprecated.Count) found) with modern alternatives"
    $md += '4. Centralize security validation in dedicated helper classes'
    $md += '5. Implement PSR-4 autoloading for better code organization'
    $md += '6. Extract repeated database queries into repository classes'
    $md += '7. Use dependency injection instead of global state'
    $md += '8. Add type hints to all function parameters and return types'
    $md += '9. Split large files into smaller, focused modules'
    $md += '10. Implement consistent error handling throughout the codebase'
    Save-Markdown (OutPath 'todo_refactoring.md') ($md -join "`n")

    # Technical debt
    $md = @()
    $md += '# Technical Debt Assessment'
    $md += ''
    $md += '## Identified Debt Areas'
    $md += ''
    $md += '| Area | Impact | Effort |'
    $md += '|---|---|---|'
    $md += '| Deprecated PHP functions | Medium | Low |'
    $md += '| Missing CSRF protection | High | Medium |'
    $md += '| Missing security headers | Medium | Low |'
    $md += '| Hardcoded credentials | Critical | Medium |'
    $md += '| Duplicate code | Medium | High |'
    $md += '| Missing error pages | Low | Low |'
    $md += '| Missing input validation | High | High |'
    $md += '| Inconsistent naming conventions | Low | Medium |'
    $md += '| Missing documentation | Medium | High |'
    $md += '| No automated tests | High | High |'
    $md += ''
    $md += '## Estimated Payoff'
    $md += '- Quick wins (Low effort): Security headers, error pages, favicon'
    $md += '- Medium term: CSRF, input validation, deprecated functions'
    $md += '- Long term: Automated tests, documentation, code refactoring'
    Save-Markdown (OutPath 'todo_technical-debt.md') ($md -join "`n")

    # Deployment checklist
    $md = @()
    $md += '# Deployment Checklist'
    $md += ''
    $md += '## Pre-Deployment'
    $md += ''
    $md += '- [ ] Fix all Critical and High severity issues'
    $md += '- [ ] Ensure .env is properly configured for production'
    $md += '- [ ] Enable HTTPS with valid SSL certificate'
    $md += '- [ ] Set up database backups'
    $md += '- [ ] Configure error logging'
    $md += '- [ ] Disable error display in production'
    $md += '- [ ] Enable OPcache'
    $md += '- [ ] Minify CSS and JavaScript'
    $md += '- [ ] Optimize images'
    $md += '- [ ] Set proper file permissions'
    $md += ''
    $md += '## Deployment Steps'
    $md += ''
    $md += '- [ ] Pull latest code from repository'
    $md += '- [ ] Run database migrations'
    $md += '- [ ] Clear cache'
    $md += '- [ ] Verify all routes work'
    $md += '- [ ] Test user authentication'
    $md += '- [ ] Test admin functionality'
    $md += '- [ ] Verify email sending'
    $md += '- [ ] Check file uploads'
    $md += '- [ ] Monitor error logs'
    $md += '- [ ] Verify SSL certificate'
    $md += ''
    $md += '## Post-Deployment'
    $md += ''
    $md += '- [ ] Monitor server performance'
    $md += '- [ ] Check for 404/500 errors'
    $md += '- [ ] Verify SEO meta tags'
    $md += '- [ ] Test on multiple browsers'
    $md += '- [ ] Test on mobile devices'
    $md += '- [ ] Verify backup system'
    $md += '- [ ] Update sitemap.xml'
    $md += '- [ ] Notify stakeholders'
    Save-Markdown (OutPath 'todo_deployment-checklist.md') ($md -join "`n")

    Write-Log 'TODO lists generated' 'INFO'
}

# ============================================================
# PHASE 14: SCORECARD & FINAL REPORTS
# ============================================================
function Invoke-Scorecard {
    Write-Color 'Phase 14 - Scorecard & Final Reports' -Color Magenta 'HEADER'
    Write-Log 'Computing scorecard and final reports' 'INFO'

    $counts = @{
        Critical = ($script:Issues | Where-Object Severity -eq 'Critical').Count
        High     = ($script:Issues | Where-Object Severity -eq 'High').Count
        Medium   = ($script:Issues | Where-Object Severity -eq 'Medium').Count
        Low      = ($script:Issues | Where-Object Severity -eq 'Low').Count
    }

    # Compute scores (weighted 0-100)
    $script:Scores.Security = [math]::Max(100 - ($counts.Critical * 25) - ($counts.High * 10) - ($counts.Medium * 5), 0)
    $script:Scores.Maintainability = [math]::Max(100 - (($script:Issues | Where-Object { $_.Type -eq 'DEPRECATED_FUNCTION' -or $_.Type -eq 'DUPLICATE_FUNCTION' -or $_.Type -eq 'DUPLICATE_CLASS' }).Count * 6), 0)
    $script:Scores.CodeQuality = [math]::Max(100 - (($script:Issues | Where-Object { $_.Type -eq 'PHP_SYNTAX' -or $_.Type -eq 'PHP_LINT' }).Count * 20), 0)

    $script:Scores.Architecture = 85
    if (-not (Test-Path (Join-Path $script:ProjectRoot 'core\Router.php'))) { $script:Scores.Architecture -= 20 }
    if (-not (Test-Path (Join-Path $script:ProjectRoot 'core\Controller.php'))) { $script:Scores.Architecture -= 10 }
    if (-not (Test-Path (Join-Path $script:ProjectRoot 'core\Model.php'))) { $script:Scores.Architecture -= 10 }

    $script:Scores.Database = 70
    $sqlCount = @(Get-ChildItem (Join-Path $script:ProjectRoot 'database') -Recurse -Filter '*.sql' -ErrorAction SilentlyContinue).Count
    if ($sqlCount -eq 0) { $script:Scores.Database -= 30 }
    elseif ($sqlCount -lt 3) { $script:Scores.Database -= 15 }

    $script:Scores.Documentation = 70
    foreach ($d in @('README.md','DEPLOYMENT.md','DATABASE_SCHEMA.md','PROJECT_CONTEXT.md')) {
        if (-not (Test-Path (Join-Path $script:ProjectRoot $d))) { $script:Scores.Documentation -= 15 }
    }

    $script:Scores.Performance = 80
    $phpFiles = Get-PhpFiles
    $largePhp = @($phpFiles | Where-Object { $_.Length -gt 100KB })
    if ($largePhp.Count -gt 5) { $script:Scores.Performance -= 15 }

    $script:Scores.Testing = 60
    $testDir = Join-Path $script:ProjectRoot 'tests'
    if (Test-Path $testDir) {
        $testFiles = @(Get-ChildItem $testDir -Recurse -File -Filter '*Test*.php' -ErrorAction SilentlyContinue)
        if ($testFiles.Count -gt 0) { $script:Scores.Testing = [math]::Min(60 + ($testFiles.Count * 5), 100) }
    }

    # Weighted overall score
    $weights = @{
        Architecture    = 0.15
        Security        = 0.20
        Performance     = 0.10
        Maintainability = 0.10
        CodeQuality     = 0.15
        Database        = 0.10
        Documentation   = 0.10
        Testing         = 0.10
    }
    $overall = 0
    foreach ($k in $script:Scores.Keys) { $overall += $script:Scores[$k] * $weights[$k] }
    $overall = [math]::Round($overall, 1)

    $grade = if ($overall -ge 90) { 'A+' } elseif ($overall -ge 80) { 'A' } elseif ($overall -ge 70) { 'B' } elseif ($overall -ge 60) { 'C' } elseif ($overall -ge 50) { 'D' } else { 'F' }

    # SCORECARD.md
    $md = @()
    $md += '# SCORECARD'
    $md += ''
    $md += '| Category | Score | Weight | Weighted Score |'
    $md += '|---|---:|---:|---:|'
    foreach ($k in $script:Scores.Keys) {
        $ws = [math]::Round($script:Scores[$k] * $weights[$k], 1)
        $md += "| $k | $($script:Scores[$k]) | $($weights[$k]) | $ws |"
    }
    $md += "| **Overall** | **$overall** | **1.00** | **$overall ($grade)** |"
    $md += ''
    $md += "## Grade: $grade"
    $md += ''
    $md += '### Grade Scale'
    $md += '- A+ (90-100): Excellent - Production ready'
    $md += '- A (80-89): Good - Minor improvements needed'
    $md += '- B (70-79): Fair - Several improvements needed'
    $md += '- C (60-69): Poor - Significant work required'
    $md += '- D (50-59): Very Poor - Major overhaul needed'
    $md += '- F (<50): Failing - Critical issues must be addressed'
    Save-Markdown (OutPath 'SCORECARD.md') ($md -join "`n")

    # PROJECT_HEALTH.md
    $md = @()
    $md += '# Project Health Assessment'
    $md += ''
    $md += "**Overall Score:** $overall/100 ($grade)"
    $md += ''
    $md += '## Health Indicators'
    $md += ''
    $md += '| Indicator | Status | Score |'
    $md += '|---|---|---|'
    $pairs = @(
        @('Security','Security'),
        @('CodeQuality','Code Quality'),
        @('Performance','Performance'),
        @('Testing','Testing'),
        @('Database','Database'),
        @('Documentation','Documentation')
    )
    foreach ($pair in $pairs) {
        $key = $pair[0]; $label = $pair[1]; $v = $script:Scores[$key]
        $status = if ($v -ge 80) { 'Good' } elseif ($v -ge 60) { 'Fair' } elseif ($v -ge 40) { 'Poor' } else { 'Critical' }
        $md += "| $label | $status | $v |"
    }
    $md += ''
    $md += '## Issue Summary'
    $md += ''
    $md += '| Severity | Count |'
    $md += '|---|---|'
    $md += "| Critical | $($counts.Critical) |"
    $md += "| High | $($counts.High) |"
    $md += "| Medium | $($counts.Medium) |"
    $md += "| Low | $($counts.Low) |"
    $md += "| **Total** | **$($script:Issues.Count)** |"
    Save-Markdown (OutPath 'PROJECT_HEALTH.md') ($md -join "`n")

    return @{ Overall = $overall; Grade = $grade; Counts = $counts }
}

function Invoke-Final {
    $scoreData = Invoke-Scorecard
    $counts = $scoreData.Counts

    # SUMMARY.md
    $phpFiles = Get-PhpFiles
    $allFiles = Get-AllFiles
    $controllers = @($allFiles | Where-Object { $_.FullName -match '\\app\\controllers\\' -and $_.Extension -eq '.php' })
    $models = @($allFiles | Where-Object { $_.FullName -match '\\app\\models\\' -and $_.Extension -eq '.php' })
    $views = @($allFiles | Where-Object { $_.FullName -match '\\app\\views\\' -and $_.Extension -eq '.php' })
    $assetsDir = Join-Path $script:ProjectRoot 'public\assets'
    $images = @(); if (Test-Path $assetsDir) { $images = @(Get-ChildItem $assetsDir -Recurse -File -Include '*.png','*.jpg','*.jpeg','*.gif','*.svg','*.webp','*.ico' -ErrorAction SilentlyContinue) }
    $sqlFiles = @($allFiles | Where-Object { $_.Extension -eq '.sql' })
    $phpErrors = ($script:Issues | Where-Object { $_.Type -eq 'PHP_SYNTAX' -or $_.Type -eq 'PHP_LINT' }).Count
    $securityIssues = ($script:Issues | Where-Object { $_.Type -match 'SECURITY|CSRF|ENV|PASSWORD|RATE' }).Count
    $performanceIssues = ($script:Issues | Where-Object { $_.Type -eq 'LARGE_ASSET' }).Count

    $md = @()
    $md += '# Audit Summary'
    $md += ''
    $md += "**Generated:** $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    $md += ''
    $md += '## Project Overview'
    $md += ''
    $md += '| Metric | Value |'
    $md += '|---|---|'
    $md += "| Total Files | $($allFiles.Count) |"
    $md += "| PHP Files | $($phpFiles.Count) |"
    $md += "| Controllers | $($controllers.Count) |"
    $md += "| Models | $($models.Count) |"
    $md += "| Views | $($views.Count) |"
    $md += "| Database Tables | $($sqlFiles.Count) SQL files |"
    $md += "| Images | $($images.Count) |"

    $md += ''
    $md += '## Issue Summary'
    $md += ''
    $md += '| Severity | Count |'
    $md += '|---|---|'
    $md += "| Critical | $($counts.Critical) |"
    $md += "| High | $($counts.High) |"
    $md += "| Medium | $($counts.Medium) |"
    $md += "| Low | $($counts.Low) |"
    $md += "| **Total** | **$($script:Issues.Count)** |"

    $md += ''
    $md += '## Quality Scores'
    $md += ''
    $md += '| Category | Score |'
    $md += '|---|---:|'
    foreach ($k in $script:Scores.Keys) { $md += "| $k | $($script:Scores[$k]) |" }
    $md += "| **Overall** | **$($scoreData.Overall)/100 ($($scoreData.Grade))** |"

    $md += ''
    $md += '## Execution'
    $md += ''
    $md += "| Metric | Value |"
    $md += '|---|---|'
    $md += "| Execution Time | $(Get-Elapsed) |"
    $md += "| Errors | $script:TotalErrors |"
    $md += "| Warnings | $script:TotalWarnings |"

    $md += ''
    $md += '## Key Findings'
    $md += "- PHP Errors: $phpErrors"
    $md += "- Security Issues: $securityIssues"
    $md += "- Performance Issues: $performanceIssues"
    $md += "- Deprecated Code: $(($script:Issues | Where-Object { $_.Type -eq 'DEPRECATED_FUNCTION' }).Count)"
    $md += "- Duplicate Code: $(($script:Issues | Where-Object { $_.Type -match 'DUPLICATE' }).Count)"

    $md += ''
    $md += '## Production Readiness'
    $md += "- **Estimated Technical Debt:** $(if($scoreData.Overall -ge 80){'Low'}elseif($scoreData.Overall -ge 60){'Medium'}else{'High'})"
    $md += "- **Estimated Fix Time:** $(if($counts.Critical -gt 0 -or $counts.High -gt 5){'Several days'}elseif($counts.High -gt 0){'1-2 days'}else{'A few hours'})"
    $md += "- **Production Ready:** $(if($scoreData.Overall -ge 70){'Yes (with minor fixes)'}else{'No (significant work required)'})"

    Save-Markdown (OutPath 'SUMMARY.md') ($md -join "`n")

    # EXECUTIVE_SUMMARY.md
    $md = @()
    $md += '# Executive Summary'
    $md += ''
    $md += "**Project:** KVN Construction Platform"
    $md += "**Audit Date:** $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    $md += "**Overall Score:** $($scoreData.Overall)/100 ($($scoreData.Grade))"
    $md += ''
    $md += '## Overview'
    $md += ''
    $md += "This audit examined $($allFiles.Count) files across the KVN Construction Platform. "
    $md += "A total of $($script:Issues.Count) issues were identified: $($counts.Critical) critical, $($counts.High) high, $($counts.Medium) medium, and $($counts.Low) low severity."
    $md += ''
    $md += '## Key Findings'
    $md += ''
    if ($counts.Critical -gt 0) { $md += "1. **Critical Issues:** $($counts.Critical) critical issues require immediate attention (hardcoded credentials, .env exposure, upload security)." }
    if ($counts.High -gt 0) { $md += "2. **High Priority:** $($counts.High) high-severity issues need to be addressed before production deployment." }
    $md += "3. **Security:** The platform has several security components but is missing CSRF protection, security headers, and proper input validation in some areas."
    $md += "4. **Code Quality:** $(if($phpErrors -gt 0){'PHP syntax errors were detected and should be fixed.'}else{'No PHP syntax errors were detected.'})"
    $md += "5. **Performance:** $(if($performanceIssues -gt 0){'Large assets were identified that should be optimized.'}else{'No major performance issues detected.'})"
    $md += ''
    $md += '## Recommendations'
    $md += ''
    $md += '1. **Immediate (Critical):** Fix hardcoded credentials, secure .env file, prevent PHP execution in uploads'
    $md += '2. **Short-term (High):** Implement CSRF protection, add security headers, fix PHP syntax errors'
    $md += '3. **Medium-term:** Add rate limiting, improve input validation, optimize assets'
    $md += '4. **Long-term:** Implement automated testing, improve documentation, refactor duplicate code'
    $md += ''
    $md += '## Conclusion'
    $md += ''
    if ($scoreData.Overall -ge 80) {
        $md += "The platform is in **good shape** with an overall score of $($scoreData.Overall)/100. "
        $md += "Addressing the identified issues will improve security and reliability."
    } elseif ($scoreData.Overall -ge 60) {
        $md += "The platform requires **moderate improvements** with an overall score of $($scoreData.Overall)/100. "
        $md += "Focus on critical and high-severity issues before production deployment."
    } else {
        $md += "The platform requires **significant improvements** with an overall score of $($scoreData.Overall)/100. "
        $md += "A comprehensive remediation plan is recommended before production deployment."
    }
    Save-Markdown (OutPath 'EXECUTIVE_SUMMARY.md') ($md -join "`n")

    # CHANGELOG.md
    $md = @()
    $md += '# Changelog'
    $md += ''
    $md += "## [1.0.0] - $(Get-Date -Format 'yyyy-MM-dd')"
    $md += ''
    $md += '### Added'
    $md += '- Initial comprehensive audit of KVN Construction Platform'
    $md += '- Environment analysis (PHP, Apache, MySQL, OS)'
    $md += '- Project inventory with CSV exports'
    $md += '- PHP code analysis (syntax, duplicates, deprecated functions)'
    $md += '- Database schema analysis'
    $md += '- Security audit (CSRF, XSS, SQLi, headers, credentials)'
    $md += '- Route and page discovery'
    $md += '- Asset analysis (missing, broken, large assets)'
    $md += '- Performance analysis (large files, SQL, caching)'
    $md += '- SEO analysis (titles, descriptions, structured data)'
    $md += '- UI framework detection and accessibility check'
    $md += '- Deployment readiness assessment'
    $md += '- Error report generation'
    $md += '- TODO lists by severity'
    $md += '- Quality scorecard with weighted scoring'
    Save-Markdown (OutPath 'CHANGELOG.md') ($md -join "`n")

    # README.md
    $md = @()
    $md += '# KVN Construction Platform - Audit Report'
    $md += ''
    $md += "**Generated:** $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    $md += "**Execution Time:** $(Get-Elapsed)"
    $md += "**Overall Score:** $($scoreData.Overall)/100 ($($scoreData.Grade))"
    $md += ''
    $md += '## Report Contents'
    $md += ''
    $md += '| File | Description |'
    $md += '|---|---|'
    $md += '| SUMMARY.md | Complete audit summary with metrics |'
    $md += '| EXECUTIVE_SUMMARY.md | High-level overview for stakeholders |'
    $md += '| SCORECARD.md | Quality scores by category |'
    $md += '| PROJECT_HEALTH.md | Health assessment and indicators |'
    $md += '| CHANGELOG.md | Audit version history |'
    $md += '| environment_*.md | Environment configuration reports |'
    $md += '| project_inventory.md | File inventory summary |'
    $md += '| php_analysis.md | PHP code analysis |'
    $md += '| database_*.md | Database schema and analysis |'
    $md += '| security_*.md | Security audit findings |'
    $md += '| routes_discovery.md | Route and page discovery |'
    $md += '| assets_analysis.md | Asset inventory and issues |'
    $md += '| performance_analysis.md | Performance analysis |'
    $md += '| seo_analysis.md | SEO analysis |'
    $md += '| ui_analysis.md | UI framework and accessibility |'
    $md += '| deployment_analysis.md | Deployment readiness |'
    $md += '| reports_*.md | Error reports (PHP, SQL, JS, HTTP) |'
    $md += '| todo_*.md | Action items by severity |'
    $md += '| inventory_*.csv | CSV inventories |'
    $md += '| audit-summary.json | Machine-readable JSON summary |'
    Save-Markdown (OutPath 'README.md') ($md -join "`n")

    # JSON summary
    $elapsedStr = Get-Elapsed
    $dateStr = (Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
    $jsonIssues = New-Object System.Collections.ArrayList
    foreach ($issue in $script:Issues) {
        $null = $jsonIssues.Add(@{
            Type     = $issue.Type
            Severity = $issue.Severity
            File     = $issue.File
            Message  = $issue.Message
        })
    }
    $json = @{
        audit = @{
            version       = '1.0.0'
            date          = $dateStr
            duration      = $elapsedStr
            overallScore  = $scoreData.Overall
            grade         = $scoreData.Grade
            projectRoot   = $script:ProjectRoot
        }
        issues = @{
            total    = $script:Issues.Count
            Critical = $counts.Critical
            High     = $counts.High
            Medium   = $counts.Medium
            Low      = $counts.Low
        }
        scores = @{
            Architecture    = $script:Scores.Architecture
            Security        = $script:Scores.Security
            Performance     = $script:Scores.Performance
            Maintainability = $script:Scores.Maintainability
            CodeQuality     = $script:Scores.CodeQuality
            Database        = $script:Scores.Database
            Documentation   = $script:Scores.Documentation
            Testing         = $script:Scores.Testing
        }
        items  = $jsonIssues
    }
    Save-Json (OutPath 'audit-summary.json') $json

    # Final reports
    # quality-score.md
    $md = @()
    $md += '# Quality Score Report'
    $md += ''
    $md += "**Overall Score:** $($scoreData.Overall)/100 ($($scoreData.Grade))"
    $md += ''
    $md += '## Category Scores'
    $md += ''
    $md += '| Category | Score | Rating |'
    $md += '|---|---|---|'
    foreach ($k in $script:Scores.Keys) {
        $v = $script:Scores[$k]
        $rating = if ($v -ge 80) { 'Excellent' } elseif ($v -ge 60) { 'Good' } elseif ($v -ge 40) { 'Fair' } else { 'Poor' }
        $md += "| $k | $v | $rating |"
    }
    $md += "| **Overall** | **$($scoreData.Overall)** | **$($scoreData.Grade)** |"
    Save-Markdown (OutPath 'quality-score.md') ($md -join "`n")

    # risk-assessment.md
    $md = @()
    $md += '# Risk Assessment'
    $md += ''
    $md += '## Risk Matrix'
    $md += ''
    $md += '| Risk Category | Level | Impact | Likelihood |'
    $md += '|---|---|---|---|'
    $riskLevels = @(
        @{ Name = 'Security Breach'; Level = if($counts.Critical -gt 0 -or $counts.High -gt 5){'High'}elseif($counts.High -gt 0){'Medium'}else{'Low'}; Impact = 'Critical'; Likelihood = if($counts.Critical -gt 0){'High'}else{'Medium'} }
        @{ Name = 'Data Loss'; Level = if($counts.Critical -gt 0){'High'}else{'Medium'}; Impact = 'Critical'; Likelihood = 'Low' }
        @{ Name = 'Application Downtime'; Level = if($phpErrors -gt 0){'High'}else{'Low'}; Impact = 'High'; Likelihood = if($phpErrors -gt 0){'Medium'}else{'Low'} }
        @{ Name = 'Performance Degradation'; Level = if($performanceIssues -gt 5){'High'}else{'Medium'}; Impact = 'Medium'; Likelihood = if($performanceIssues -gt 5){'Medium'}else{'Low'} }
    )
    foreach ($rl in $riskLevels) {
        $md += "| $($rl.Name) | $($rl.Level) | $($rl.Impact) | $($rl.Likelihood) |"
    }
    $md += ''
    $md += '## Risk Mitigation'
    $md += ''
    $md += '| Risk | Mitigation Strategy |'
    $md += '|---|---|'
    $md += '| Security Breach | Implement CSRF, security headers, input validation, rate limiting |'
    $md += '| Data Loss | Regular automated backups, database replication |'
    $md += '| Application Downtime | Proper error handling, monitoring, CI/CD pipeline |'
    $md += '| Performance Degradation | OPcache, asset optimization, database indexing |'
    Save-Markdown (OutPath 'risk-assessment.md') ($md -join "`n")

    # production-checklist.md
    $md = @()
    $md += '# Production Checklist'
    $md += ''
    $md += '## Security'
    $md += ''
    $md += '- [ ] All Critical and High issues resolved'
    $md += '- [ ] CSRF protection implemented on all forms'
    $md += '- [ ] Security headers configured (CSP, X-Frame-Options, etc.)'
    $md += '- [ ] HTTPS enabled with valid SSL certificate'
    $md += '- [ ] .env file excluded from version control'
    $md += '- [ ] Password hashing using bcrypt/argon2'
    $md += '- [ ] Rate limiting on authentication endpoints'
    $md += '- [ ] File upload validation and sanitization'
    $md += '- [ ] SQL injection prevention (prepared statements)'
    $md += '- [ ] XSS prevention (output escaping)'
    $md += ''
    $md += '## Performance'
    $md += ''
    $md += '- [ ] OPcache enabled'
    $md += '- [ ] Static assets minified and compressed'
    $md += '- [ ] Images optimized'
    $md += '- [ ] Database indexes added'
    $md += '- [ ] Caching headers configured'
    $md += '- [ ] CDN configured for static assets'
    $md += ''
    $md += '## Monitoring'
    $md += ''
    $md += '- [ ] Error logging configured'
    $md += '- [ ] Server monitoring set up'
    $md += '- [ ] Backup system operational'
    $md += '- [ ] Cron jobs configured'
    $md += '- [ ] Alert system configured'
    Save-Markdown (OutPath 'production-checklist.md') ($md -join "`n")

    # release-checklist.md
    $md = @()
    $md += '# Release Checklist'
    $md += ''
    $md += '## Pre-Release'
    $md += ''
    $md += '- [ ] All tests passing'
    $md += '- [ ] Code review completed'
    $md += '- [ ] Security audit passed'
    $md += '- [ ] Performance tested'
    $md += '- [ ] Database migrations tested'
    $md += '- [ ] Backup verified'
    $md += '- [ ] Rollback plan documented'
    $md += ''
    $md += '## Release Day'
    $md += ''
    $md += '- [ ] Deploy to staging environment'
    $md += '- [ ] Run smoke tests'
    $md += '- [ ] Deploy to production'
    $md += '- [ ] Verify deployment'
    $md += '- [ ] Monitor error logs'
    $md += '- [ ] Monitor performance'
    $md += ''
    $md += '## Post-Release'
    $md += ''
    $md += '- [ ] Verify all functionality'
    $md += '- [ ] Check for 404/500 errors'
    $md += '- [ ] Verify SSL certificate'
    $md += '- [ ] Update documentation'
    $md += '- [ ] Notify stakeholders'
    Save-Markdown (OutPath 'release-checklist.md') ($md -join "`n")

    # FINAL_SIGNOFF.md
    $md = @()
    $md += '# Final Sign-Off'
    $md += ''
    $md += '| Item | Detail |'
    $md += '|---|---|'
    $md += "| Project | KVN Construction Platform |"
    $md += "| Audit Date | $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') |"
    $md += "| Execution Time | $(Get-Elapsed) |"
    $md += "| Overall Score | $($scoreData.Overall)/100 ($($scoreData.Grade)) |"
    $md += "| Total Issues | $($script:Issues.Count) |"
    $md += "| Critical Issues | $($counts.Critical) |"
    $md += "| High Issues | $($counts.High) |"
    $md += "| Medium Issues | $($counts.Medium) |"
    $md += "| Low Issues | $($counts.Low) |"
    $md += ''
    $md += '## Sign-Off'
    $md += ''
    $md += '- [ ] All Critical and High issues resolved'
    $md += '- [ ] Security measures implemented'
    $md += '- [ ] Performance optimizations applied'
    $md += '- [ ] Backup and monitoring configured'
    $md += '- [ ] Documentation updated'
    $md += '- [ ] Stakeholders notified'
    $md += ''
    $md += '---'
    $md += ''
    $md += '*This audit report was generated automatically by the KVN Construction Audit Script.*'
    Save-Markdown (OutPath 'FINAL_SIGNOFF.md') ($md -join "`n")

    Write-Color "Audit complete. Overall: $($scoreData.Overall)/100 ($($scoreData.Grade))" -Color Green 'OK'
    Write-Color "Execution time: $(Get-Elapsed)" -Color DarkCyan 'PROGRESS'
    Write-Color "Total issues: $($script:Issues.Count) (Critical: $($counts.Critical), High: $($counts.High), Medium: $($counts.Medium), Low: $($counts.Low))" -Color Cyan 'INFO'
    Write-Log "Audit complete. Overall=$($scoreData.Overall) Grade=$($scoreData.Grade) Issues=$($script:Issues.Count)" 'INFO'
}

# ============================================================
# MAIN EXECUTION
# ============================================================
try {
    Invoke-Environment
    Invoke-Inventory
    Invoke-PhpAudit
    Invoke-Database
    Invoke-Security
    Invoke-Routes
    Invoke-Assets
    Invoke-Performance
    Invoke-Seo
    Invoke-Ui
    Invoke-Deployment
    Invoke-ErrorReports
    Invoke-TodoGenerator
    Invoke-Final
} catch {
    Write-Color "FATAL ERROR (continuing best-effort): $($_.Exception.Message)" -Color Red 'ERROR'
    Write-Log "FATAL: $($_.Exception.Message)" 'ERROR'
    Write-Color "Stack trace: $($_.ScriptStackTrace)" -Color Red 'ERROR'
}

Write-Color "Script finished at $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -Color Gray 'INFO'
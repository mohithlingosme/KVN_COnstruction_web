# Create-only scaffolding: uploads protection, composer.json, env example, helper/middleware dirs
# Safe policy:
# - Never overwrites existing files
# - Creates missing folders/files only

$ErrorActionPreference = 'Stop'

function EnsureDir([string]$p) {
  if (-not (Test-Path -LiteralPath $p -PathType Container)) {
    New-Item -ItemType Directory -Force -Path $p | Out-Null
    $true
  } else { $false }
}

function EnsureFile([string]$p, [string]$value) {
  if (-not (Test-Path -LiteralPath $p -PathType Leaf)) {
    $dir = [System.IO.Path]::GetDirectoryName($p)
    if ($dir -and -not (Test-Path -LiteralPath $dir -PathType Container)) {
      New-Item -ItemType Directory -Force -Path $dir | Out-Null
    }
    New-Item -ItemType File -Force -Path $p | Out-Null
    Set-Content -LiteralPath $p -Value $value -Encoding UTF8
    $true
  } else { $false }
}

$htValue = @"
php_flag engine off
Options -Indexes
"@

$report = New-Object System.Collections.Generic.List[string]

# public uploads protection
if (EnsureDir './public/assets/uploads') { $report.Add('CREATED DIR: ./public/assets/uploads') | Out-Null }
if (EnsureFile './public/assets/uploads/.htaccess' $htValue) { $report.Add('CREATED FILE: ./public/assets/uploads/.htaccess') | Out-Null }

# admin uploads protection + subfolders
if (EnsureFile './admin/uploads/.htaccess' $htValue) { $report.Add('CREATED FILE: ./admin/uploads/.htaccess') | Out-Null }
if (EnsureFile './admin/uploads/clients/.htaccess' $htValue) { $report.Add('CREATED FILE: ./admin/uploads/clients/.htaccess') | Out-Null }
if (EnsureFile './admin/uploads/projects/.htaccess' $htValue) { $report.Add('CREATED FILE: ./admin/uploads/projects/.htaccess') | Out-Null }
if (EnsureFile './admin/uploads/quotations/.htaccess' $htValue) { $report.Add('CREATED FILE: ./admin/uploads/quotations/.htaccess') | Out-Null }

# admin root htaccess (security)
if (EnsureFile './admin/.htaccess' $htValue) { $report.Add('CREATED FILE: ./admin/.htaccess') | Out-Null }

# composer.json (create-only)
$composerPath = './composer.json'
if (-not (Test-Path -LiteralPath $composerPath -PathType Leaf)) {
  $composerValue = @'
{
  "name": "kvn/construction-erp",
  "require": {
    "dompdf/dompdf": "^3.0",
    "vlucas/phpdotenv": "^5.6"
  }
}
'@
  EnsureFile $composerPath $composerValue | Out-Null | Out-Null
  $report.Add('CREATED FILE: ./composer.json') | Out-Null
}

# .env.example (create-only)
$envExPath = './.env.example'
if (-not (Test-Path -LiteralPath $envExPath -PathType Leaf)) {
  $envExValue = @"
DB_HOST=localhost
DB_NAME=kvnc
DB_USER=root
DB_PASS=
WHATSAPP_API_KEY=
APP_URL=http://localhost/KVN_Construction
"@
  EnsureFile $envExPath $envExValue | Out-Null | Out-Null
  $report.Add('CREATED FILE: ./.env.example') | Out-Null
}

# helper + middleware dirs
if (EnsureDir './admin/includes/helpers') { $report.Add('CREATED DIR: ./admin/includes/helpers') | Out-Null }
if (EnsureDir './admin/includes/middleware') { $report.Add('CREATED DIR: ./admin/includes/middleware') | Out-Null }

'--- SECURITY SCAFFOLD REPORT ---'
$report | ForEach-Object { $_ }
'--- DONE ---'

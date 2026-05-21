# Structure-only thorough checks (no behavior changes)
$ErrorActionPreference = 'Stop'

function Exists($p) {
  return (Test-Path -LiteralPath $p)
}

Write-Host 'Thorough pre-runtime checks (structure only)'
Write-Host ''

$paths = @(
  './public/index.php',
  './public/login.php',
  './public/estimator.php',
  './public/contact.php',
  './admin/dashboard.php',
  './admin/includes/auth.php',
  './admin/includes/db.php',

  './public/.htaccess',

  './api/packages/get.php',
  './api/packages/create.php',
  './api/packages/update.php',
  './api/packages/delete.php',
  './api/whatsapp/webhook.php',

  './storage/logs/.keep',
  './storage/backups/.keep',
  './storage/temp/.keep',

  './database/migrations/.keep',
  './database/seeds/.keep'
)

Write-Host '1) Key file existence checks:'
foreach ($p in $paths) {
  Write-Host ('- {0}: {1}' -f $p, (Exists $p))
}

Write-Host ''
Write-Host '2) Directory existence checks:'
$dirs = @(
  './public',
  './public/assets/css',
  './public/assets/js',
  './public/assets/images',
  './admin/assets/css',
  './admin/assets/js',
  './admin/includes',
  './api/packages',
  './api/whatsapp',
  './storage/logs',
  './database/migrations',
  './docs'
)
foreach ($d in $dirs) {
  Write-Host ('- {0}: {1}' -f $d, (Exists $d))
}

Write-Host ''
Write-Host '3) Note on case-sensitivity risk:'
Write-Host '- Both ./public/assets/JS (existing) and ./public/assets/js (created) may exist. Check references in HTML/JS.'

$content = Get-Content resources/views/partials/kodebooking-page.blade.php -Raw

# Ganti semua pola r.xxx || '' menjadi r.xxx != null ? r.xxx : ''
$content = $content -replace "r\.(\w+) \|\| ''", 'r.$1 != null ? r.$1 : '''

$content | Set-Content resources/views/partials/kodebooking-page.blade.php
Write-Host "Selesai"
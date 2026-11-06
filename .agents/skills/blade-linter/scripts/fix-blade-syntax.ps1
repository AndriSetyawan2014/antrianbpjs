# Script Pindai & Perbaiki Sintaks Blade Template
# Penggunaan:
#   powershell.exe -NoProfile -ExecutionPolicy Bypass -File .agents/skills/blade-linter/scripts/fix-blade-syntax.ps1 [-CheckOnly]

param(
    [switch]$CheckOnly = $false
)

$viewsDir = Join-Path $PSScriptRoot "..\..\..\..\resources\views"
$viewsDir = [System.IO.Path]::GetFullPath($viewsDir)

if (-not (Test-Path $viewsDir)) {
    Write-Error "Direktori views tidak ditemukan di: $viewsDir"
    exit 1
}

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " Memeriksa Sintaks Blade Template..." -ForegroundColor Cyan
Write-Host "   Direktori: $viewsDir"
Write-Host "=========================================="

$files = Get-ChildItem -Path $viewsDir -Recurse -Filter "*.blade.php" | Where-Object { $_.Name -ne "welcome.blade.php" }
$issuesFound = 0
$filesFixed = 0

foreach ($f in $files) {
    $text = [System.IO.File]::ReadAllText($f.FullName, [System.Text.Encoding]::UTF8)
    $originalText = $text
    $fileHasIssue = $false

    # 1. Deteksi / Perbaiki {@-- ... --@} atau {@-- ... --}}
    if ($text.Contains('{@--') -or $text.Contains('--@}')) {
        $fileHasIssue = $true
        $issuesFound++
        Write-Warning "Pola invalid {@-- atau --@} ditemukan di: $($f.Name)"
        $text = $text.Replace('{@--', '{{--').Replace('--@}', '--}}')
    }

    # 2. Deteksi kurung kurawal ganjil pada komentar: {-- tanpa pasangan {{--
    $patternSingleBrace = '(?<!\{)\{--(?!\{)'
    if ([System.Text.RegularExpressions.Regex]::IsMatch($text, $patternSingleBrace)) {
        $fileHasIssue = $true
        $issuesFound++
        Write-Warning "Pola single-brace comment '{--' ditemukan di: $($f.Name)"
        $text = [System.Text.RegularExpressions.Regex]::Replace($text, $patternSingleBrace, '{{--')
    }

    # 3. Deteksi kurung kurawal ganjil penutup: --} tanpa pasangan --}}
    $patternSingleClose = '(?<!-)--\}(?!\})'
    if ([System.Text.RegularExpressions.Regex]::IsMatch($text, $patternSingleClose)) {
        $fileHasIssue = $true
        $issuesFound++
        Write-Warning "Pola single-brace close '--}' ditemukan di: $($f.Name)"
        $text = [System.Text.RegularExpressions.Regex]::Replace($text, $patternSingleClose, '--}}')
    }

    # 4. Deteksi @endpushOnce tanpa @pushOnce
    if ($text.Contains('@endpushOnce') -and -not $text.Contains('@pushOnce')) {
        $fileHasIssue = $true
        $issuesFound++
        Write-Warning "Pola mismatch '@endpushOnce' tanpa '@pushOnce' ditemukan di: $($f.Name)"
        $text = $text.Replace('@endpushOnce', '@endpush')
    }

    # 5. Normalisasi jika ada --}}} akibat replace berulang
    if ($text.Contains('--}}}')) {
        $text = $text.Replace('--}}}', '--}}')
    }

    # Simpan jika ada perubahan dan tidak CheckOnly
    if ($fileHasIssue) {
        if (-not $CheckOnly -and $text -ne $originalText) {
            [System.IO.File]::WriteAllText($f.FullName, $text, [System.Text.Encoding]::UTF8)
            Write-Host "  -> Berhasil diperbaiki otomatis: $($f.FullName)" -ForegroundColor Green
            $filesFixed++
        }
    }
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
if ($issuesFound -eq 0) {
    Write-Host " SEMUA FILE BLADE BERSIH & VALID!" -ForegroundColor Green
    exit 0
} else {
    if ($CheckOnly) {
        Write-Host " Ditemukan $issuesFound masalah sintaks pada Blade views." -ForegroundColor Red
        exit 1
    } else {
        Write-Host " Ditemukan $issuesFound masalah dan $filesFixed file telah diperbaiki otomatis." -ForegroundColor Green
        exit 0
    }
}


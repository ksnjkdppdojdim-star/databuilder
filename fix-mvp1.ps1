# DataBuilder MVP1 - Quick Fix Script

$basePath = "c:\Users\Jules\Documents\iMSCP\databuilder"
$blockPath = "$basePath\src\Block\Admin\IndexBlock.php"
$blockNewPath = "$basePath\src\Block\Admin\IndexBlock-new.php"
$zipPath = "c:\Users\Jules\Documents\iMSCP\DataBuilderMVP1-v3.zip"

Write-Host "`n" -ForegroundColor Cyan
Write-Host "DataBuilder MVP1 - Fix Script" -ForegroundColor Cyan
Write-Host "=" -Split '' | ForEach-Object {[void](-join $_)} | Write-Host -ForegroundColor Cyan
Write-Host " " -ForegroundColor Cyan

# Step 1: Replace IndexBlock.php
Write-Host "Step 1: Replacing IndexBlock.php..." -ForegroundColor Yellow

if (-not (Test-Path $blockNewPath)) {
    Write-Host "ERROR: $blockNewPath not found" -ForegroundColor Red
    exit 1
}

# Backup old file
if (Test-Path $blockPath) {
    $timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
    $backup = "$blockPath.backup-$timestamp"
    Copy-Item $blockPath $backup
    Write-Host "  - Backup: $backup" -ForegroundColor Green
    Remove-Item $blockPath
}

# Copy new file
Copy-Item $blockNewPath $blockPath
Write-Host "  - Done: IndexBlock.php replaced" -ForegroundColor Green

# Step 2: Build ZIP
Write-Host "`nStep 2: Creating ZIP archive..." -ForegroundColor Yellow

if (Test-Path $zipPath) {
    Remove-Item $zipPath
}

Compress-Archive -Path "$basePath\*" -DestinationPath $zipPath -Force
$size = (Get-Item $zipPath).Length / 1MB
Write-Host "  - File: $zipPath" -ForegroundColor Green
Write-Host "  - Size: $([Math]::Round($size, 2)) MB" -ForegroundColor Green

# Step 3: Summary
Write-Host "`nStep 3: Installation ready!" -ForegroundColor Green
Write-Host " " -ForegroundColor Cyan
Write-Host "NEXT STEPS:" -ForegroundColor Cyan
Write-Host "  1. Go to iMSCP Admin > Plugins" -ForegroundColor White
Write-Host "  2. Upload: $zipPath" -ForegroundColor White
Write-Host "  3. Install and Enable" -ForegroundColor White
Write-Host "  4. Visit: http://your-imscp/admin/index" -ForegroundColor White
Write-Host "  5. Press F12 and run: window.DataBuilderDebug.getStats()" -ForegroundColor White
Write-Host " " -ForegroundColor Cyan

# DataBuilder MVP1 - Installation Script
# Script d'installation et de vérification des fichiers

param(
    [switch]$Fix = $false,
    [switch]$Verify = $false,
    [switch]$BuildZip = $false
)

$basePath = "c:\Users\Jules\Documents\iMSCP\databuilder"
$blockPath = "$basePath\src\Block\Admin\IndexBlock.php"
$blockNewPath = "$basePath\src\Block\Admin\IndexBlock-new.php"

Write-Host "`n🔧 DataBuilder MVP1 - Installation Helper`n" -ForegroundColor Cyan

# ============================================================================
# 1. VERIFY - Vérifier l'état des fichiers
# ============================================================================

if ($Verify -or (!$Fix -and !$BuildZip)) {
    Write-Host "📋 Vérification des fichiers..." -ForegroundColor Yellow
    
    $files = @(
        @{ Path = "$basePath\src\Core\Engine.php"; Name = "Engine.php"; Must = $true }
        @{ Path = "$basePath\src\Block\Admin\IndexBlock.php"; Name = "IndexBlock.php (OLD)"; Must = $false }
        @{ Path = "$basePath\src\Block\Admin\IndexBlock-new.php"; Name = "IndexBlock-new.php (NEW)"; Must = $true }
        @{ Path = "$basePath\src\Integration\ImscpBridge.php"; Name = "ImscpBridge.php"; Must = $true }
        @{ Path = "$basePath\src\Cleanup\CleanupManager.php"; Name = "CleanupManager.php"; Must = $true }
        @{ Path = "$basePath\Plugins\DataBuilderIMSCPBetaPlugin\asset-serve.php"; Name = "asset-serve.php"; Must = $true }
        @{ Path = "$basePath\themes\default\css\databuilder.css"; Name = "databuilder.css"; Must = $true }
        @{ Path = "$basePath\themes\default\js\databuilder.js"; Name = "databuilder.js"; Must = $true }
    )
    
    $allOk = $true
    foreach ($file in $files) {
        if (Test-Path $file.Path) {
            Write-Host "  ✅ $(($file.Name).PadRight(35)) found" -ForegroundColor Green
        } else {
            if ($file.Must) {
                Write-Host "  ❌ $(($file.Name).PadRight(35)) MISSING (required!)" -ForegroundColor Red
                $allOk = $false
            } else {
                Write-Host "  ⚠️  $(($file.Name).PadRight(35)) not found (optional)" -ForegroundColor Yellow
            }
        }
    }
    
    if ($allOk) {
        Write-Host "`n✨ All required files present!" -ForegroundColor Green
    } else {
        Write-Host "`n⛔ Some required files missing!" -ForegroundColor Red
    }
}

# ============================================================================
# 2. FIX - Remplacer IndexBlock.php
# ============================================================================

if ($Fix) {
    Write-Host "`n🔄 Fixing IndexBlock.php..." -ForegroundColor Yellow
    
    # Vérifier que le nouveau fichier existe
    if (-not (Test-Path $blockNewPath)) {
        Write-Host "❌ ERROR: $blockNewPath not found" -ForegroundColor Red
        exit 1
    }
    
    # Sauvegarder l'ancien fichier
    if (Test-Path $blockPath) {
        $backup = "$blockPath.backup-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
        Copy-Item $blockPath $backup
        Write-Host "✅ Backup created: $backup" -ForegroundColor Green
        
        # Supprimer l'ancien
        Remove-Item $blockPath
        Write-Host "✅ Old IndexBlock.php removed" -ForegroundColor Green
    }
    
    # Copier le nouveau
    Copy-Item $blockNewPath $blockPath
    Write-Host "✅ New IndexBlock.php installed" -ForegroundColor Green
    
    # Vérifier que le nouveau contient ImscpBridge
    $content = Get-Content $blockPath -Raw
    if ($content -match 'ImscpBridge' -and $content -match 'use DataBuilder\\Integration\\ImscpBridge') {
        Write-Host "✅ IndexBlock.php contains ImscpBridge reference" -ForegroundColor Green
    } else {
        Write-Host "❌ WARNING: IndexBlock.php may not use ImscpBridge correctly" -ForegroundColor Yellow
    }
}

# ============================================================================
# 3. BUILD ZIP - Créer le ZIP pour installation
# ============================================================================

if ($BuildZip) {
    Write-Host "`n📦 Building ZIP archive..." -ForegroundColor Yellow
    
    $zipPath = "c:\Users\Jules\Documents\iMSCP\DataBuilderMVP1-v3.zip"
    
    # Vérifier que le répertoire existe
    if (-not (Test-Path $basePath)) {
        Write-Host "❌ ERROR: $basePath not found" -ForegroundColor Red
        exit 1
    }
    
    # Créer le ZIP
    try {
        if (Test-Path $zipPath) {
            Remove-Item $zipPath
            Write-Host "✅ Old ZIP removed" -ForegroundColor Green
        }
        
        Compress-Archive -Path "$basePath\*" -DestinationPath $zipPath -Force
        $size = (Get-Item $zipPath).Length / 1MB
        Write-Host "✅ ZIP created: $zipPath" -ForegroundColor Green
        Write-Host "   Size: $([Math]::Round($size, 2)) MB" -ForegroundColor Cyan
    } catch {
        Write-Host "❌ ERROR creating ZIP: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

# ============================================================================
# Summary
# ============================================================================

Write-Host "`n" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "📝 NEXT STEPS:" -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan

if ($Fix -or $BuildZip) {
    Write-Host "`n1. Install in iMSCP:" -ForegroundColor Cyan
    Write-Host "   - Admin → Plugins" -ForegroundColor White
    Write-Host "   - Upload: $zipPath" -ForegroundColor White
    Write-Host "   - Install → Enable" -ForegroundColor White
    
    Write-Host "`n2. Test the page:" -ForegroundColor Cyan
    Write-Host "   - Navigate to: http://your-imscp/admin/index" -ForegroundColor White
    Write-Host "   - Should show admin dashboard with design" -ForegroundColor White
    
    Write-Host "`n3. Debug (F12 Console):" -ForegroundColor Cyan
    Write-Host "   - window.DataBuilderDebug.getStats()" -ForegroundColor White
    Write-Host "   - window.DataBuilderDebug.getColors()" -ForegroundColor White
} else {
    Write-Host "`nUsage:" -ForegroundColor Yellow
    Write-Host "  .\install.ps1 -Verify  # Check files" -ForegroundColor White
    Write-Host "  .\install.ps1 -Fix     # Replace IndexBlock.php" -ForegroundColor White
    Write-Host "  .\install.ps1 -BuildZip # Create installation ZIP" -ForegroundColor White
    Write-Host "  .\install.ps1 -Fix -BuildZip # Do both" -ForegroundColor White
}

Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Cyan

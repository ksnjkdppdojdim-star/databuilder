# DataBuilder Plugin Status Reset Script
# Resets plugin status to allow clean deletion and re-installation

param(
    [string]$DbHost = "localhost",
    [string]$DbName = "imscp",
    [string]$DbUser = "imscp_user",
    [string]$DbPassword = ""
)

Write-Host "DataBuilder Plugin Status Reset"
Write-Host "================================`n"

# Check for MySQL CLI installation
$mysqlPath = Get-Command mysql.exe -ErrorAction SilentlyContinue
if (-not $mysqlPath) {
    Write-Host "MySQL CLI not found. Attempting via PHP instead..."
    
    # Use PHP to execute the SQL commands
    $phpScript = @'
<?php
// Read iMSCP config to get database credentials
$configFile = 'C:\Users\Jules\Documents\iMSCP\imscp\gui\data\imscp.conf.php';

if (!file_exists($configFile)) {
    echo "Config file not found! Path: $configFile\n";
    exit(1);
}

// Include config but suppress any output
ob_start();
include $configFile;
ob_end_clean();

// Check if we have the expected variables
if (!isset($DATABASE) || !is_array($DATABASE)) {
    echo "Could not read database configuration from imscp.conf.php\n";
    exit(1);
}

$dbHost = $DATABASE['HOST'] ?? 'localhost';
$dbName = $DATABASE['NAME'] ?? 'imscp';
$dbUser = $DATABASE['USER'] ?? 'imscp_user';
$dbPass = $DATABASE['PASS'] ?? '';

echo "Database: $dbName at $dbHost\n";

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]
    );
    echo "✓ Connected to database\n\n";
    
    // Check current status
    $stmt = $pdo->query("SELECT plugin_name, plugin_status, plugin_backend, plugin_error FROM plugin WHERE plugin_name = 'DataBuilderIMSCPBetaPlugin'");
    $plugin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($plugin) {
        echo "Current Plugin Status:\n";
        echo "  Name: " . $plugin['plugin_name'] . "\n";
        echo "  Status: " . $plugin['plugin_status'] . "\n";
        echo "  Backend: " . $plugin['plugin_backend'] . "\n";
        echo "  Error: " . ($plugin['plugin_error'] ?? 'None') . "\n\n";
        
        // Reset status to 'disabled' which allows deletion
        echo "Resetting plugin status to 'disabled'...\n";
        $pdo->prepare("UPDATE plugin SET plugin_status = 'disabled', plugin_error = NULL WHERE plugin_name = 'DataBuilderIMSCPBetaPlugin'")->execute();
        
        // Verify update
        $stmt = $pdo->query("SELECT plugin_status FROM plugin WHERE plugin_name = 'DataBuilderIMSCPBetaPlugin'");
        $newStatus = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "✓ Plugin status reset to: " . $newStatus['plugin_status'] . "\n";
        echo "\nYou can now DELETE the plugin from iMSCP Admin Panel.\n";
        
    } else {
        echo "ERROR: Plugin 'DataBuilderIMSCPBetaPlugin' not found in database.\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "ERROR: Could not connect to database\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
'@

    # Write PHP script to temp file and execute
    $tempPhpFile = [System.IO.Path]::GetTempFileName() -replace '\.tmp$', '.php'
    Set-Content -Path $tempPhpFile -Value $phpScript -Encoding UTF8
    
    Write-Host "Executing database reset via PHP...`n"
    
    # Execute PHP script
    php.exe $tempPhpFile
    $result = $?
    
    # Cleanup temp file
    Remove-Item $tempPhpFile -Force -ErrorAction SilentlyContinue
    
    if ($result) {
        Write-Host "`n✓ Plugin status reset successfully!"
    } else {
        Write-Host "`n✗ Failed to reset plugin status"
        exit 1
    }
    
} else {
    Write-Host "Using MySQL CLI: $($mysqlPath.Source)"
    
    # Use mysql CLI
    $sqlQuery = @"
SELECT plugin_name, plugin_status FROM plugin WHERE plugin_name = 'DataBuilderIMSCPBetaPlugin';

UPDATE plugin SET plugin_status = 'disabled', plugin_error = NULL WHERE plugin_name = 'DataBuilderIMSCPBetaPlugin';

SELECT plugin_name, plugin_status FROM plugin WHERE plugin_name = 'DataBuilderIMSCPBetaPlugin';
"@

    $sqlQuery | mysql.exe -h $DbHost -u $DbUser -p"$DbPassword" $DbName
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "`n✓ Plugin status reset successfully!"
    } else {
        Write-Host "`n✗ Failed to reset plugin status. Make sure database credentials are correct."
        exit 1
    }
}

Write-Host "`nNext Steps:`n"
Write-Host "1. Go to Admin Panel → Plugins"
Write-Host "2. Find DataBuilderIMSCPBetaPlugin"
Write-Host "3. Click DELETE button`n"

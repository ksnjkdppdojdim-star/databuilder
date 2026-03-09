<?php
/**
 * Simple test to verify route loading
 */

// Basic bootstrap
$root = dirname(__DIR__, 3);
require $root . '/gui/include/imscp-lib.php';

// Verify admin
redirectIfNotAdmin();

echo '<div style="padding: 20px; background: #efe; border: 1px solid #0a0;">';
echo '<h1>✓ DataBuilder Server Stats Route is WORKING!</h1>';
echo '<p>Admin user: ' . htmlspecialchars($_SESSION['user_id'] ?? 'Unknown') . '</p>';
echo '<p>Current dir: ' . htmlspecialchars(getcwd()) . '</p>';
echo '<p>Version: ' . PHP_VERSION . '</p>';
echo '</div>';

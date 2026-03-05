<?php
/**
 * DataBuilder Static Assets Endpoint
 * 
 * This file serves CSS, JS, and other static assets from the DataBuilder plugin
 * without exposing the full plugin directory structure
 * 
 * URL: http://imscp/admin/databuilder-asset?file=css/databuilder.css
 * or:  http://imscp/admin/databuilder-asset/css/databuilder.css
 */

// Whitelist of allowed files to serve
$allowedAssets = [
    'css/databuilder.css' => 'text/css',
    'js/databuilder.js' => 'application/javascript',
    'admin.css' => 'text/css',
    'admin.js' => 'application/javascript',
];

// Get requested file
$requestedFile = $_GET['file'] ?? basename($_SERVER['REQUEST_URI']);
$requestedFile = str_replace(['..', '\\'], ['', '/'], $requestedFile); // Security: prevent directory traversal

// Check if file is whitelisted
if (!isset($allowedAssets[$requestedFile])) {
    http_response_code(404);
    die('Asset not found');
}

// Build file path
$basePath = dirname(__DIR__) . '/themes/default/' . ltrim($requestedFile, '/');

// Security: verify file exists and is within allowed directory
if (!file_exists($basePath) || strpos(realpath($basePath), realpath(dirname(__DIR__) . '/themes/default/')) !== 0) {
    http_response_code(403);
    die('Access denied');
}

// Serve the file
header('Content-Type: ' . $allowedAssets[$requestedFile]);
header('Cache-Control: public, max-age=86400'); // Cache for 24 hours
header('Access-Control-Allow-Origin: *'); // Allow CORS

// Add GZIP compression if available
if (function_exists('gzencode') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
    ob_start('ob_gzhandler');
}

readfile($basePath);

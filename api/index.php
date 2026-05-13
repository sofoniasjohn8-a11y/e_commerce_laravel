<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// CORS preflight
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = [
    'http://localhost:5173',
    'http://localhost:3000',
    'https://efrontend-ovutl5aak-sofoniasjohn8-a11ys-projects.vercel.app',
    'https://efrontend-ten.vercel.app',
    'https://e-commerce-front-seven-orcin.vercel.app',
];

if (in_array($origin, $allowed)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Create writable dirs in /tmp for Vercel's read-only filesystem
$tmpBase = '/tmp/laravel';
foreach ([
    "$tmpBase/storage/framework/cache/data",
    "$tmpBase/storage/framework/sessions",
    "$tmpBase/storage/framework/views",
    "$tmpBase/storage/framework/testing",
    "$tmpBase/storage/logs",
    "$tmpBase/storage/app/public",
    "$tmpBase/bootstrap/cache",
] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0775, true);
}

require __DIR__ . '/../vendor/autoload.php';

// Vercel strips /api prefix when routing to api/index.php
// Restore the full path so Laravel routing works correctly
if (isset($_SERVER['REQUEST_URI']) && !str_starts_with($_SERVER['REQUEST_URI'], '/api')) {
    $_SERVER['REQUEST_URI'] = '/api' . $_SERVER['REQUEST_URI'];
    $_SERVER['PATH_INFO'] = '/api' . ($_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI']);
}

// DEBUG: show what URI Laravel sees
if (isset($_GET['debug_uri'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'not set',
        'PATH_INFO' => $_SERVER['PATH_INFO'] ?? 'not set',
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'not set',
        'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'not set',
    ]);
    exit;
}

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

try {
    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
}
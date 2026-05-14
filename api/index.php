<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;

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

// Fix SCRIPT_NAME so Laravel resolves /api routes correctly
if (!str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api')) {
    $_SERVER['REQUEST_URI'] = '/api' . ($_SERVER['REQUEST_URI'] ?? '/');
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$request = Request::capture();

try {
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage(), 'file' => basename($e->getFile()), 'line' => $e->getLine()]);
}

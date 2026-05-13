<?php

define('LARAVEL_START', microtime(true));

// Handle CORS preflight before Laravel boots
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

// Use .env.production on Vercel
$envFile = file_exists(__DIR__ . '/../.env.production') ? '.env.production' : '.env';
$_ENV['APP_ENV_FILE'] = $envFile;
putenv('APP_ENV_FILE=' . $envFile);

// Copy .env.production to .env if it doesn't exist
if (!file_exists(__DIR__ . '/../.env') && file_exists(__DIR__ . '/../.env.production')) {
    copy(__DIR__ . '/../.env.production', __DIR__ . '/../.env');
}

// Manually load .env.production into environment if .env doesn't exist
if (!file_exists(__DIR__ . '/../.env') && file_exists(__DIR__ . '/../.env.production')) {
    $lines = file(__DIR__ . '/../.env.production', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);

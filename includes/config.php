<?php
// Load environment variables (simple .env loader)
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $_ENV[$k] = $v;
    }
}

define('APP_NAME', $_ENV['APP_NAME'] ?? 'Pods Store');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_URL', $_ENV['APP_URL'] ?? '');
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'pods_store');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');
define('TIMEZONE', $_ENV['TIMEZONE'] ?? 'UTC');

// Optional external service keys (never hardcode secrets here)
define('ONESIGNAL_APP_ID', $_ENV['ONESIGNAL_APP_ID'] ?? '');
define('ONESIGNAL_REST_API_KEY', $_ENV['ONESIGNAL_REST_API_KEY'] ?? '');
define('GEMINI_API_KEY', $_ENV['GEMINI_API_KEY'] ?? '');

date_default_timezone_set(TIMEZONE);

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');
header('X-XSS-Protection: 1; mode=block');



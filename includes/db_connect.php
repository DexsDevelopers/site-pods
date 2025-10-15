<?php
// /includes/db_connect.php
// Se existir um override local com credenciais fixas (não versionado), usa-o.
if (file_exists(__DIR__ . '/db_connect.local.php')) {
    require __DIR__ . '/db_connect.local.php';
    return;
}

require_once __DIR__ . '/config.php';

date_default_timezone_set(TIMEZONE ?: 'America/Sao_Paulo');

// Chaves externas vêm do .env via includes/config.php
// ONESIGNAL_APP_ID, ONESIGNAL_REST_API_KEY, GEMINI_API_KEY

$host = DB_HOST;
$dbname = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;
$charset = DB_CHARSET;

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("SET time_zone = '-03:00'");
} catch (PDOException $e) {
    $logPath = __DIR__ . '/../logs/error.log';
    error_log('[' . date('Y-m-d H:i:s') . "] [ERROR] [db_connect] " . $e->getMessage(), 3, $logPath);
    if (defined('APP_ENV') && APP_ENV !== 'production') {
        echo 'Erro ao conectar ao banco: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    } else {
        echo 'Erro ao conectar ao banco de dados.';
    }
    http_response_code(500);
    exit;
}



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

$host = 'localhost';
$dbname = 'u853242961_loja_pods';
$user = 'u853242961_pods_saluc';
$pass = 'Lucastav8012@';
$charset = 'utf8mb4';

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
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}



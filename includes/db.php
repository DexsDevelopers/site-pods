<?php
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (Throwable $e) {
    $logPath = __DIR__ . '/../logs/error.log';
    error_log('[' . date('Y-m-d H:i:s') . "] [ERROR] [db.php] " . $e->getMessage(), 3, $logPath);
    http_response_code(500);
    if (APP_ENV !== 'production') {
        echo 'Erro ao conectar ao banco de dados: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    } else {
        echo 'Erro ao conectar ao banco de dados.';
    }
    exit;
}



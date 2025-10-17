<?php
/**
 * ========================================
 * CONFIGURAÇÃO CENTRAL DA APLICAÇÃO
 * ========================================
 * 
 * Carrega variáveis de ambiente e configura
 * a aplicação de forma segura e centralizada.
 */

// Detecta o diretório raiz do projeto
define('PROJECT_ROOT', dirname(dirname(__FILE__)));

// Carrega arquivo .env
$envFile = PROJECT_ROOT . '/.env';
if (!file_exists($envFile)) {
    die('❌ Erro: Arquivo .env não encontrado. Copie .env.example para .env e configure os valores.');
}

// Função para carregar variáveis do .env
function loadEnv($file) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0) continue;
        
        // Processa linhas com formato KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove aspas se existirem
            if (in_array($value[0] ?? null, ['"', "'"])) {
                $value = substr($value, 1, -1);
            }
            
            $_ENV[$key] = $value;
        }
    }
}

loadEnv($envFile);

// ========================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ========================================
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
define('DB_NAME', $_ENV['DB_NAME'] ?? 'techvapor_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// DSN para PDO
define('DATABASE_URL', 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

// ========================================
// CONFIGURAÇÕES DA APLICAÇÃO
// ========================================
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Wazzy Vape');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');

// Converte string para booleano
define('DEBUG_MODE', strtolower(APP_DEBUG) === 'true' || APP_DEBUG === 1);

// ========================================
// CONFIGURAÇÕES DE SESSÃO
// ========================================
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 3600));
define('CSRF_TOKEN_LENGTH', (int)($_ENV['CSRF_TOKEN_LENGTH'] ?? 32));
define('HASH_ALGORITHM', $_ENV['HASH_ALGORITHM'] ?? 'bcrypt');

// ========================================
// CONFIGURAÇÕES DE LOGS
// ========================================
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'info');
define('LOG_PATH', PROJECT_ROOT . '/' . ($_ENV['LOG_PATH'] ?? 'logs/'));

// Cria diretório de logs se não existir
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// ========================================
// CONFIGURAÇÕES DE UPLOAD
// ========================================
define('UPLOAD_PATH', PROJECT_ROOT . '/uploads/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// ========================================
// FUSO HORÁRIO
// ========================================
date_default_timezone_set('America/Sao_Paulo');

// ========================================
// MODO DE PRODUÇÃO
// ========================================
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', DEBUG_MODE ? 1 : 0);
}

// ========================================
// INICIA SESSÃO
// ========================================
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => APP_URL !== 'http://localhost',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

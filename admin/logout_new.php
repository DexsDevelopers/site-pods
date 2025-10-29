<?php
session_start();

// Log de logout se estiver logado
if (isset($_SESSION['admin_id'])) {
    // Conexão direta com banco
    $host = 'localhost';
    $db = 'u853242961_loja_pods';
    $user = 'u853242961_pods_saluc';
    $pass = 'Lucastav8012@';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Log de logout
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, acao, ip, user_agent) VALUES (?, 'logout', ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown']);
    } catch (Exception $e) {
        // Ignorar erro de log
    }
}

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Redirecionar para login
header('Location: login.php');
exit;
?>

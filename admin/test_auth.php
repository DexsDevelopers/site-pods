<?php
// Teste de autenticação
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h2>Teste de Autenticação</h2>";

// Verificar se está logado
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo "<p style='color: green;'>✅ Usuário logado!</p>";
    echo "<p><strong>Nome:</strong> " . ($_SESSION['admin_nome'] ?? 'N/A') . "</p>";
    echo "<p><strong>Email:</strong> " . ($_SESSION['admin_email'] ?? 'N/A') . "</p>";
    echo "<p><strong>ID:</strong> " . ($_SESSION['admin_id'] ?? 'N/A') . "</p>";
    echo "<p><a href='index.php'>Ir para Dashboard</a> | <a href='logout.php'>Sair</a></p>";
} else {
    echo "<p style='color: red;'>❌ Usuário não logado!</p>";
    echo "<p><a href='login.php'>Fazer Login</a></p>";
}

echo "<h3>Informações da Sessão:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>

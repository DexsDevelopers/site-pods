<?php
// Script para configurar sistema de admin
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexão direta com banco
$host = 'localhost';
$db = 'u853242961_loja_pods';
$user = 'u853242961_pods_saluc';
$pass = 'Lucastav8012@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Configurando Sistema de Admin</h2>";
    
    // Criar tabela de administradores
    $sql = "
    CREATE TABLE IF NOT EXISTS admin_users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        ativo BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($sql);
    echo "<p>✅ Tabela 'admin_users' criada ou já existe.</p>";
    
    // Verificar se já existe admin
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM admin_users");
    $count = $stmt->fetch()['total'];
    
    if ($count == 0) {
        // Criar admin padrão
        $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute(['Administrador', 'admin@wazzypods.com', $senha_hash]);
        echo "<p>✅ Admin padrão criado:</p>";
        echo "<p><strong>Email:</strong> admin@wazzypods.com</p>";
        echo "<p><strong>Senha:</strong> admin123</p>";
    } else {
        echo "<p>ℹ️ Já existem administradores cadastrados.</p>";
    }
    
    echo "<p>🎉 Sistema de admin configurado com sucesso!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>

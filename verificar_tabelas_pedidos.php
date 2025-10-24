<?php
require_once 'includes/config_hostinger.php';
require_once 'includes/db.php';

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Verificar Tabelas de Pedidos - Wazzy Pods</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #1e293b; color: #e2e8f0; }";
echo "h2, h3 { color: #8b5cf6; }";
echo "table { border-collapse: collapse; width: 100%; margin: 10px 0; }";
echo "th, td { border: 1px solid #475569; padding: 8px; text-align: left; }";
echo "th { background: #334155; color: #8b5cf6; }";
echo ".success { color: #10b981; }";
echo ".error { color: #ef4444; }";
echo ".info { color: #3b82f6; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<h2>🔍 Verificando Tabelas de Pedidos</h2>";

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Verificar se a tabela orders existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() == 0) {
        echo "<p class='error'>❌ Tabela 'orders' não existe!</p>";
        
        // Criar tabela orders
        $pdo->exec("
            CREATE TABLE orders (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nome VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                telefone VARCHAR(20) NULL,
                cep VARCHAR(10) NOT NULL,
                endereco VARCHAR(500) NOT NULL,
                numero VARCHAR(20) NULL,
                complemento VARCHAR(200) NULL,
                bairro VARCHAR(100) NULL,
                cidade VARCHAR(100) NOT NULL,
                estado VARCHAR(2) NOT NULL,
                total DECIMAL(10,2) NOT NULL,
                status ENUM('pending', 'paid', 'shipped', 'delivered', 'cancelled', 'refunded', 'unpaid') DEFAULT 'unpaid',
                order_number VARCHAR(50) NULL,
                mercadopago_preference_id VARCHAR(255) NULL,
                mercadopago_payment_id VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        echo "<p class='success'>✅ Tabela 'orders' criada com sucesso!</p>";
    } else {
        echo "<p class='success'>✅ Tabela 'orders' já existe.</p>";
    }
    
    // Verificar se a tabela order_items existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    if ($stmt->rowCount() == 0) {
        echo "<p class='error'>❌ Tabela 'order_items' não existe!</p>";
        
        // Criar tabela order_items
        $pdo->exec("
            CREATE TABLE order_items (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT NOT NULL,
                product_id INT NULL,
                product_name VARCHAR(255) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                quantity INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        echo "<p class='success'>✅ Tabela 'order_items' criada com sucesso!</p>";
    } else {
        echo "<p class='success'>✅ Tabela 'order_items' já existe.</p>";
    }
    
    // Verificar estrutura da tabela orders
    echo "<h3>📋 Estrutura da tabela orders:</h3>";
    $stmt = $pdo->query("DESCRIBE orders");
    echo "<table border='1' style='width:100%; text-align:left;'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
    
    // Verificar estrutura da tabela order_items
    echo "<h3>📋 Estrutura da tabela order_items:</h3>";
    $stmt = $pdo->query("DESCRIBE order_items");
    echo "<table border='1' style='width:100%; text-align:left;'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
    
    // Contar registros
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    echo "<p><strong>Total de pedidos:</strong> {$totalOrders}</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM order_items");
    $totalItems = $stmt->fetch()['total'];
    echo "<p><strong>Total de itens:</strong> {$totalItems}</p>";
    
    echo "<p class='success'>🎉 Verificação concluída com sucesso!</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "</body>";
echo "</html>";
?>

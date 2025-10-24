<?php
echo "<h1>🔍 Verificando Tabelas de Pedidos</h1>";

try {
    require_once 'includes/config_hostinger.php';
    require_once 'includes/db.php';
    
    echo "<p style='color: green;'>✅ Conexão com banco funcionando!</p>";
    
    $pdo = Database::getInstance()->getConnection();
    
    // Verificar se a tabela orders existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela 'orders' não existe!</p>";
        
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
        echo "<p style='color: green;'>✅ Tabela 'orders' criada com sucesso!</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabela 'orders' já existe.</p>";
    }
    
    // Verificar se a tabela order_items existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela 'order_items' não existe!</p>";
        
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
        echo "<p style='color: green;'>✅ Tabela 'order_items' criada com sucesso!</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabela 'order_items' já existe.</p>";
    }
    
    // Contar registros
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    echo "<p><strong>Total de pedidos:</strong> {$totalOrders}</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM order_items");
    $totalItems = $stmt->fetch()['total'];
    echo "<p><strong>Total de itens:</strong> {$totalItems}</p>";
    
    echo "<p style='color: green;'>🎉 Verificação concluída com sucesso!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>

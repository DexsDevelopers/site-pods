<?php
require_once 'includes/config_hostinger.php';
require_once 'includes/db.php';

try {
    echo "<h2>🔧 Corrigindo Tabela de Pedidos</h2>";
    
    // Verificar se a tabela orders existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Tabela 'orders' não existe. Criando...<br>";
        
        // Criar tabela orders
        $sql = "
        CREATE TABLE orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            telefone VARCHAR(20) NOT NULL,
            endereco VARCHAR(500) NOT NULL,
            numero VARCHAR(20) NOT NULL,
            complemento VARCHAR(200),
            bairro VARCHAR(100) NOT NULL,
            cidade VARCHAR(100) NOT NULL,
            estado VARCHAR(2) NOT NULL,
            cep VARCHAR(10) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
            mercado_pago_preference_id VARCHAR(255) NULL,
            mercado_pago_payment_id VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $pdo->exec($sql);
        echo "✅ Tabela 'orders' criada!<br>";
    } else {
        echo "✅ Tabela 'orders' já existe.<br>";
        
        // Verificar colunas existentes
        $stmt = $pdo->query("DESCRIBE orders");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = [
            'nome' => 'VARCHAR(255) NOT NULL',
            'email' => 'VARCHAR(255) NOT NULL',
            'telefone' => 'VARCHAR(20) NOT NULL',
            'endereco' => 'VARCHAR(500) NOT NULL',
            'numero' => 'VARCHAR(20) NOT NULL',
            'complemento' => 'VARCHAR(200)',
            'bairro' => 'VARCHAR(100) NOT NULL',
            'cidade' => 'VARCHAR(100) NOT NULL',
            'estado' => 'VARCHAR(2) NOT NULL',
            'cep' => 'VARCHAR(10) NOT NULL',
            'total' => 'DECIMAL(10,2) NOT NULL',
            'status' => "ENUM('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending'"
        ];
        
        foreach ($requiredColumns as $column => $definition) {
            if (!in_array($column, $columns)) {
                echo "➕ Adicionando coluna '$column'...<br>";
                $pdo->exec("ALTER TABLE orders ADD COLUMN $column $definition");
                echo "✅ Coluna '$column' adicionada!<br>";
            } else {
                echo "ℹ️ Coluna '$column' já existe.<br>";
            }
        }
    }
    
    // Verificar se a tabela order_items existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "➕ Criando tabela 'order_items'...<br>";
        
        $sql = "
        CREATE TABLE order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            quantity INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $pdo->exec($sql);
        echo "✅ Tabela 'order_items' criada!<br>";
    } else {
        echo "✅ Tabela 'order_items' já existe.<br>";
    }
    
    echo "<br><h3>🎉 Estrutura Corrigida!</h3>";
    echo "<p>As tabelas de pedidos estão prontas para uso.</p>";
    echo "<p><a href='pages/checkout.php'>Testar Checkout</a></p>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

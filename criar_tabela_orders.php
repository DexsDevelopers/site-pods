<?php
require_once 'includes/config_hostinger.php';
require_once 'includes/db.php';

try {
    echo "<h2>🔧 Criando Tabela de Pedidos</h2>";
    
    // Criar tabela orders
    $sql = "
    CREATE TABLE IF NOT EXISTS orders (
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_email (email),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($sql);
    echo "✅ Tabela 'orders' criada com sucesso!<br>";
    
    // Criar tabela order_items
    $sql = "
    CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        INDEX idx_order_id (order_id),
        INDEX idx_product_id (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($sql);
    echo "✅ Tabela 'order_items' criada com sucesso!<br>";
    
    echo "<br><h3>🎉 Estrutura de Pedidos Criada!</h3>";
    echo "<p>As tabelas 'orders' e 'order_items' foram criadas com sucesso.</p>";
    echo "<p><a href='admin/pedidos.php'>Ver Pedidos</a> | <a href='pages/checkout.php'>Testar Checkout</a></p>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

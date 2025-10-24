<?php
require_once 'includes/config_hostinger.php';
require_once 'includes/db.php';

try {
    echo "<h2>🔧 Corrigindo Foreign Keys da Tabela Orders</h2>";
    
    // Verificar constraints existentes
    $stmt = $pdo->query("SHOW CREATE TABLE orders");
    $createTable = $stmt->fetch();
    
    echo "<h3>📋 Estrutura atual da tabela orders:</h3>";
    echo "<pre>" . htmlspecialchars($createTable[1]) . "</pre>";
    
    // Remover foreign key constraints problemáticas
    echo "<h3>🔧 Removendo constraints problemáticas...</h3>";
    
    try {
        // Remover constraint de user_id se existir
        $pdo->exec("ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_1");
        echo "✅ Constraint 'orders_ibfk_1' removida!<br>";
    } catch (Exception $e) {
        echo "ℹ️ Constraint 'orders_ibfk_1' não existe ou já foi removida.<br>";
    }
    
    try {
        // Remover constraint de user_id se existir (outro nome possível)
        $pdo->exec("ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_2");
        echo "✅ Constraint 'orders_ibfk_2' removida!<br>";
    } catch (Exception $e) {
        echo "ℹ️ Constraint 'orders_ibfk_2' não existe ou já foi removida.<br>";
    }
    
    // Verificar se existe coluna user_id e removê-la se não for necessária
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasUserId = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'user_id') {
            $hasUserId = true;
            break;
        }
    }
    
    if ($hasUserId) {
        echo "<h3>🗑️ Removendo coluna user_id desnecessária...</h3>";
        try {
            $pdo->exec("ALTER TABLE orders DROP COLUMN user_id");
            echo "✅ Coluna 'user_id' removida!<br>";
        } catch (Exception $e) {
            echo "⚠️ Erro ao remover coluna 'user_id': " . $e->getMessage() . "<br>";
        }
    } else {
        echo "ℹ️ Coluna 'user_id' não existe.<br>";
    }
    
    // Garantir que todas as colunas necessárias existam
    echo "<h3>✅ Verificando colunas necessárias...</h3>";
    
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
        'status' => "ENUM('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending'",
        'mercado_pago_preference_id' => 'VARCHAR(255) NULL',
        'mercado_pago_payment_id' => 'VARCHAR(255) NULL'
    ];
    
    $stmt = $pdo->query("DESCRIBE orders");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($requiredColumns as $column => $definition) {
        if (!in_array($column, $existingColumns)) {
            echo "➕ Adicionando coluna '$column'...<br>";
            try {
                $pdo->exec("ALTER TABLE orders ADD COLUMN $column $definition");
                echo "✅ Coluna '$column' adicionada!<br>";
            } catch (Exception $e) {
                echo "❌ Erro ao adicionar coluna '$column': " . $e->getMessage() . "<br>";
            }
        } else {
            echo "ℹ️ Coluna '$column' já existe.<br>";
        }
    }
    
    // Verificar se a tabela order_items existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<h3>➕ Criando tabela order_items...</h3>";
        
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
    echo "<p>As foreign keys problemáticas foram removidas e a estrutura está pronta.</p>";
    echo "<p><a href='pages/checkout.php'>Testar Checkout</a></p>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

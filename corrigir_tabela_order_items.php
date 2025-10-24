<?php
require_once 'includes/config_hostinger.php';
require_once 'includes/db.php';

try {
    echo "<h2>🔧 Corrigindo Tabela Order Items</h2>";
    
    // Verificar se a tabela order_items existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Tabela 'order_items' não existe. Criando...<br>";
        
        // Criar tabela order_items
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
        
        // Verificar estrutura atual
        $stmt = $pdo->query("DESCRIBE order_items");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Colunas existentes:</h3>";
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>";
        }
        
        // Verificar se as colunas necessárias existem
        $requiredColumns = [
            'price' => 'DECIMAL(10,2) NOT NULL',
            'quantity' => 'INT NOT NULL',
            'product_name' => 'VARCHAR(255) NOT NULL',
            'product_id' => 'INT NOT NULL',
            'order_id' => 'INT NOT NULL'
        ];
        
        $existingColumns = array_column($columns, 'Field');
        
        echo "<h3>🔧 Adicionando colunas faltantes...</h3>";
        
        foreach ($requiredColumns as $column => $definition) {
            if (!in_array($column, $existingColumns)) {
                echo "➕ Adicionando coluna '$column'...<br>";
                try {
                    $pdo->exec("ALTER TABLE order_items ADD COLUMN $column $definition");
                    echo "✅ Coluna '$column' adicionada!<br>";
                } catch (Exception $e) {
                    echo "❌ Erro ao adicionar coluna '$column': " . $e->getMessage() . "<br>";
                }
            } else {
                echo "ℹ️ Coluna '$column' já existe.<br>";
            }
        }
        
        // Verificar se há foreign key constraint
        $stmt = $pdo->query("SHOW CREATE TABLE order_items");
        $createTable = $stmt->fetch();
        
        if (strpos($createTable[1], 'FOREIGN KEY') === false) {
            echo "<h3>🔗 Adicionando foreign key constraint...</h3>";
            try {
                $pdo->exec("ALTER TABLE order_items ADD CONSTRAINT fk_order_items_order_id FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE");
                echo "✅ Foreign key constraint adicionada!<br>";
            } catch (Exception $e) {
                echo "⚠️ Erro ao adicionar foreign key: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "ℹ️ Foreign key constraint já existe.<br>";
        }
    }
    
    // Verificar estrutura final
    echo "<h3>📊 Estrutura final da tabela order_items:</h3>";
    $stmt = $pdo->query("DESCRIBE order_items");
    $finalColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($finalColumns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h3>🎉 Estrutura Corrigida!</h3>";
    echo "<p>A tabela 'order_items' está pronta para uso.</p>";
    echo "<p><a href='pages/checkout.php'>Testar Checkout</a></p>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

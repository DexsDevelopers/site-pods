<?php
require_once 'includes/config_hostinger.php';
require_once 'includes/db.php';

try {
    echo "<h2>🔧 Corrigindo Foreign Keys da Tabela Order Items</h2>";
    
    // Verificar se a tabela order_items existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Tabela 'order_items' não existe. Criando...<br>";
        
        // Criar tabela order_items sem foreign key para product_id
        $sql = "
        CREATE TABLE order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT NOT NULL,
            product_id INT,
            product_name VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            quantity INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $pdo->exec($sql);
        echo "✅ Tabela 'order_items' criada sem foreign key para product_id!<br>";
    } else {
        echo "✅ Tabela 'order_items' já existe.<br>";
        
        // Verificar constraints existentes
        $stmt = $pdo->query("SHOW CREATE TABLE order_items");
        $createTable = $stmt->fetch();
        
        echo "<h3>📋 Estrutura atual da tabela order_items:</h3>";
        echo "<pre>" . htmlspecialchars($createTable[1]) . "</pre>";
        
        // Remover foreign key constraints problemáticas
        echo "<h3>🔧 Removendo constraints problemáticas...</h3>";
        
        try {
            // Remover constraint de product_id se existir
            $pdo->exec("ALTER TABLE order_items DROP FOREIGN KEY order_items_ibfk_2");
            echo "✅ Constraint 'order_items_ibfk_2' removida!<br>";
        } catch (Exception $e) {
            echo "ℹ️ Constraint 'order_items_ibfk_2' não existe ou já foi removida.<br>";
        }
        
        try {
            // Remover constraint de product_id se existir (outro nome possível)
            $pdo->exec("ALTER TABLE order_items DROP FOREIGN KEY order_items_ibfk_1");
            echo "✅ Constraint 'order_items_ibfk_1' removida!<br>";
        } catch (Exception $e) {
            echo "ℹ️ Constraint 'order_items_ibfk_1' não existe ou já foi removida.<br>";
        }
        
        // Verificar se existe coluna product_id e torná-la opcional
        $stmt = $pdo->query("DESCRIBE order_items");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $hasProductId = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'product_id') {
                $hasProductId = true;
                break;
            }
        }
        
        if ($hasProductId) {
            echo "<h3>🔧 Tornando product_id opcional...</h3>";
            try {
                $pdo->exec("ALTER TABLE order_items MODIFY COLUMN product_id INT NULL");
                echo "✅ Coluna 'product_id' alterada para permitir NULL!<br>";
            } catch (Exception $e) {
                echo "⚠️ Erro ao alterar coluna 'product_id': " . $e->getMessage() . "<br>";
            }
        } else {
            echo "ℹ️ Coluna 'product_id' não existe.<br>";
        }
        
        // Garantir que todas as colunas necessárias existam
        echo "<h3>✅ Verificando colunas necessárias...</h3>";
        
        $requiredColumns = [
            'order_id' => 'INT NOT NULL',
            'product_id' => 'INT NULL',
            'product_name' => 'VARCHAR(255) NOT NULL',
            'price' => 'DECIMAL(10,2) NOT NULL',
            'quantity' => 'INT NOT NULL'
        ];
        
        $existingColumns = array_column($columns, 'Field');
        
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
        
        // Garantir que a foreign key para order_id existe
        $stmt = $pdo->query("SHOW CREATE TABLE order_items");
        $createTable = $stmt->fetch();
        
        if (strpos($createTable[1], 'FOREIGN KEY') === false) {
            echo "<h3>🔗 Adicionando foreign key para order_id...</h3>";
            try {
                $pdo->exec("ALTER TABLE order_items ADD CONSTRAINT fk_order_items_order_id FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE");
                echo "✅ Foreign key para order_id adicionada!<br>";
            } catch (Exception $e) {
                echo "⚠️ Erro ao adicionar foreign key para order_id: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "ℹ️ Foreign key para order_id já existe.<br>";
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
    
    echo "<br><h3>🎉 Problema Corrigido!</h3>";
    echo "<p>A foreign key problemática foi removida e a estrutura está pronta.</p>";
    echo "<p><a href='pages/checkout.php'>Testar Checkout</a></p>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

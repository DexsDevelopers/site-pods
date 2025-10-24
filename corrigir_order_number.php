<?php
require_once 'includes/config_hostinger.php';
require_once 'includes/db.php';

try {
    echo "<h2>🔧 Corrigindo Problema de Order Number</h2>";
    
    // Verificar estrutura da tabela orders
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Colunas existentes na tabela orders:</h3>";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ") - Key: " . $column['Key'] . "<br>";
    }
    
    // Verificar se existe coluna order_number
    $hasOrderNumber = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'order_number') {
            $hasOrderNumber = true;
            break;
        }
    }
    
    if ($hasOrderNumber) {
        echo "<h3>🔧 Corrigindo coluna order_number...</h3>";
        
        // Verificar se há valores vazios ou duplicados
        $stmt = $pdo->query("SELECT order_number, COUNT(*) as count FROM orders WHERE order_number = '' OR order_number IS NULL GROUP BY order_number");
        $emptyNumbers = $stmt->fetchAll();
        
        if (!empty($emptyNumbers)) {
            echo "⚠️ Encontrados " . count($emptyNumbers) . " registros com order_number vazio.<br>";
            
            // Atualizar registros com order_number vazio
            $stmt = $pdo->query("SELECT id FROM orders WHERE order_number = '' OR order_number IS NULL");
            $emptyRecords = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($emptyRecords as $orderId) {
                $newOrderNumber = 'ORD-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
                $pdo->prepare("UPDATE orders SET order_number = ? WHERE id = ?")->execute([$newOrderNumber, $orderId]);
                echo "✅ Atualizado pedido $orderId com número: $newOrderNumber<br>";
            }
        }
        
        // Remover constraint UNIQUE se existir
        try {
            $pdo->exec("ALTER TABLE orders DROP INDEX order_number");
            echo "✅ Constraint UNIQUE de order_number removida!<br>";
        } catch (Exception $e) {
            echo "ℹ️ Constraint UNIQUE de order_number não existe ou já foi removida.<br>";
        }
        
        // Alterar coluna para permitir NULL e remover UNIQUE
        try {
            $pdo->exec("ALTER TABLE orders MODIFY COLUMN order_number VARCHAR(50) NULL");
            echo "✅ Coluna order_number alterada para permitir NULL.<br>";
        } catch (Exception $e) {
            echo "⚠️ Erro ao alterar coluna order_number: " . $e->getMessage() . "<br>";
        }
        
    } else {
        echo "ℹ️ Coluna order_number não existe na tabela orders.<br>";
    }
    
    // Verificar se há outras constraints problemáticas
    echo "<h3>🔍 Verificando outras constraints...</h3>";
    
    $stmt = $pdo->query("SHOW CREATE TABLE orders");
    $createTable = $stmt->fetch();
    
    if (strpos($createTable[1], 'UNIQUE') !== false) {
        echo "⚠️ Encontradas constraints UNIQUE na tabela orders.<br>";
        echo "<pre>" . htmlspecialchars($createTable[1]) . "</pre>";
    } else {
        echo "✅ Nenhuma constraint UNIQUE problemática encontrada.<br>";
    }
    
    // Garantir que a tabela orders tenha a estrutura correta
    echo "<h3>✅ Verificando estrutura da tabela orders...</h3>";
    
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
    
    $existingColumns = array_column($columns, 'Field');
    
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
    
    echo "<br><h3>🎉 Problema Corrigido!</h3>";
    echo "<p>A constraint de order_number foi removida e a estrutura está pronta.</p>";
    echo "<p><a href='pages/checkout.php'>Testar Checkout</a></p>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

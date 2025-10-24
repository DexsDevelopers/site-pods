<?php
echo "<h1>📊 Status das Tabelas de Pedidos</h1>";

try {
    require_once 'includes/config_hostinger.php';
    require_once 'includes/db.php';
    
    $pdo = Database::getInstance()->getConnection();
    
    // Verificar tabela orders
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabela 'orders' existe</p>";
        
        // Mostrar estrutura
        $stmt = $pdo->query("DESCRIBE orders");
        echo "<h3>Estrutura da tabela orders:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $totalOrders = $stmt->fetch()['total'];
        echo "<p><strong>Total de pedidos:</strong> {$totalOrders}</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabela 'orders' não existe</p>";
    }
    
    // Verificar tabela order_items
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabela 'order_items' existe</p>";
        
        // Mostrar estrutura
        $stmt = $pdo->query("DESCRIBE order_items");
        echo "<h3>Estrutura da tabela order_items:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM order_items");
        $totalItems = $stmt->fetch()['total'];
        echo "<p><strong>Total de itens:</strong> {$totalItems}</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabela 'order_items' não existe</p>";
    }
    
    echo "<p style='color: green;'>🎉 Verificação completa!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>

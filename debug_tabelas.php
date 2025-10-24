<?php
echo "<h1>🔍 Debug das Tabelas</h1>";

echo "<p>1. Iniciando debug...</p>";

try {
    echo "<p>2. Incluindo config_hostinger.php...</p>";
    require_once 'includes/config_hostinger.php';
    echo "<p style='color: green;'>✅ Config incluído!</p>";
    
    echo "<p>3. Incluindo db.php...</p>";
    require_once 'includes/db.php';
    echo "<p style='color: green;'>✅ DB incluído!</p>";
    
    echo "<p>4. Criando conexão...</p>";
    $pdo = Database::getInstance()->getConnection();
    echo "<p style='color: green;'>✅ Conexão criada!</p>";
    
    echo "<p>5. Testando consulta simples...</p>";
    $stmt = $pdo->query("SELECT 1 as teste");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Consulta funcionando! Resultado: " . $result['teste'] . "</p>";
    
    echo "<p>6. Verificando tabelas...</p>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Tabelas encontradas:</strong></p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
    echo "<p>7. Verificando tabela orders...</p>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabela 'orders' existe!</p>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $totalOrders = $stmt->fetch()['total'];
        echo "<p><strong>Total de pedidos:</strong> {$totalOrders}</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabela 'orders' não existe!</p>";
    }
    
    echo "<p>8. Verificando tabela order_items...</p>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabela 'order_items' existe!</p>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM order_items");
        $totalItems = $stmt->fetch()['total'];
        echo "<p><strong>Total de itens:</strong> {$totalItems}</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabela 'order_items' não existe!</p>";
    }
    
    echo "<p style='color: green;'>🎉 Debug completo!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'>Arquivo: " . $e->getFile() . "</p>";
    echo "<p style='color: red;'>Linha: " . $e->getLine() . "</p>";
    echo "<p style='color: red;'>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>

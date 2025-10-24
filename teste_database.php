<?php
echo "<h1>🔍 Teste da Classe Database</h1>";

echo "<p>1. Iniciando teste...</p>";

try {
    echo "<p>2. Incluindo config_hostinger.php...</p>";
    require_once 'includes/config_hostinger.php';
    echo "<p style='color: green;'>✅ Config incluído!</p>";
    
    echo "<p>3. Incluindo db.php...</p>";
    require_once 'includes/db.php';
    echo "<p style='color: green;'>✅ DB incluído!</p>";
    
    echo "<p>4. Testando classe Database...</p>";
    $pdo = Database::getConnection();
    echo "<p style='color: green;'>✅ Classe Database funcionando!</p>";
    
    echo "<p>5. Testando consulta...</p>";
    $stmt = $pdo->query("SELECT 1 as teste");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Consulta funcionando! Resultado: " . $result['teste'] . "</p>";
    
    echo "<p>6. Verificando tabela orders...</p>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    echo "<p><strong>Total de pedidos:</strong> {$totalOrders}</p>";
    
    echo "<p>7. Verificando tabela order_items...</p>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM order_items");
    $totalItems = $stmt->fetch()['total'];
    echo "<p><strong>Total de itens:</strong> {$totalItems}</p>";
    
    echo "<p style='color: green;'>🎉 Classe Database funcionando!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'>Arquivo: " . $e->getFile() . "</p>";
    echo "<p style='color: red;'>Linha: " . $e->getLine() . "</p>";
}
?>

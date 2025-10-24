<?php
echo "<h1>🔍 Teste de Conexão Direta</h1>";

echo "<p>1. Iniciando teste...</p>";

try {
    echo "<p>2. Incluindo config_hostinger.php...</p>";
    require_once 'includes/config_hostinger.php';
    echo "<p style='color: green;'>✅ Config incluído!</p>";
    
    echo "<p>3. Verificando constantes...</p>";
    echo "<p>DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NÃO DEFINIDA') . "</p>";
    echo "<p>DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NÃO DEFINIDA') . "</p>";
    echo "<p>DB_USER: " . (defined('DB_USER') ? DB_USER : 'NÃO DEFINIDA') . "</p>";
    echo "<p>DATABASE_URL: " . (defined('DATABASE_URL') ? DATABASE_URL : 'NÃO DEFINIDA') . "</p>";
    
    echo "<p>4. Testando conexão direta...</p>";
    $host = 'localhost';
    $db = 'u853242961_loja_pods';
    $user = 'u853242961_pods_saluc';
    $pass = 'Lucastav8012@';
    
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "<p style='color: green;'>✅ Conexão direta funcionando!</p>";
    
    echo "<p>5. Testando consulta...</p>";
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
    
    echo "<p style='color: green;'>🎉 Teste completo!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'>Arquivo: " . $e->getFile() . "</p>";
    echo "<p style='color: red;'>Linha: " . $e->getLine() . "</p>";
}
?>

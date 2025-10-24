<?php
echo "<h1>Teste PHP Funcionando!</h1>";
echo "<p>Data atual: " . date('d/m/Y H:i:s') . "</p>";

try {
    require_once 'includes/config_hostinger.php';
    require_once 'includes/db.php';
    
    echo "<p style='color: green;'>✅ Conexão com banco funcionando!</p>";
    
    $pdo = Database::getInstance()->getConnection();
    echo "<p style='color: green;'>✅ PDO criado com sucesso!</p>";
    
    // Testar consulta simples
    $stmt = $pdo->query("SELECT 1 as teste");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Consulta SQL funcionando! Resultado: " . $result['teste'] . "</p>";
    
    // Verificar tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Tabelas encontradas:</strong></p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>

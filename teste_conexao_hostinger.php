<?php
/**
 * Teste de Conexão - Hostinger
 * Script simples para testar se a conexão com o banco está funcionando
 */

echo "<h2>🔗 Teste de Conexão - Hostinger</h2>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Configurações da Hostinger
$host = 'localhost';
$db = 'u853242961_loja_pods';
$user = 'u853242961_pods_saluc';
$pass = 'Lucastav8012@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green; font-size: 18px;'>✅ <strong>Conexão com banco estabelecida com sucesso!</strong></p>";
    
    // Teste básico
    $stmt = $pdo->query("SELECT 1 as teste");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Query de teste executada: " . $result['teste'] . "</p>";
    
    // Verificar tabelas
    echo "<h3>📋 Tabelas no banco:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = [];
    while ($row = $stmt->fetch()) {
        $tabelas[] = $row[0];
    }
    
    if (count($tabelas) > 0) {
        echo "<ul>";
        foreach ($tabelas as $tabela) {
            echo "<li style='color: green;'>✅ $tabela</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhuma tabela encontrada</p>";
    }
    
    // Verificar se tabela produtos existe
    if (in_array('produtos', $tabelas)) {
        echo "<p style='color: green;'>✅ Tabela 'produtos' encontrada!</p>";
        
        // Verificar estrutura
        $stmt = $pdo->query("DESCRIBE produtos");
        echo "<h4>Estrutura da tabela produtos:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Tabela 'produtos' NÃO encontrada!</p>";
    }
    
    echo "<hr>";
    echo "<p style='color: green;'><strong>🎉 Tudo funcionando perfeitamente!</strong></p>";
    echo "<p>Você pode apagar este arquivo após usar.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-size: 18px;'>❌ <strong>Erro de conexão:</strong> " . $e->getMessage() . "</p>";
    echo "<h3>💡 Possíveis soluções:</h3>";
    echo "<ul>";
    echo "<li>Verifique se as credenciais estão corretas</li>";
    echo "<li>Verifique se o banco de dados existe</li>";
    echo "<li>Verifique se o usuário tem permissões</li>";
    echo "<li>Verifique se o servidor MySQL está rodando</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Erro geral:</strong> " . $e->getMessage() . "</p>";
}
?>

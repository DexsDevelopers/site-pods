<?php
/**
 * Diagnóstico específico para problema com tabela 'key' no painel de produtos
 */

// Configurações do banco (usando as credenciais corretas)
$host = 'localhost';
$db = 'u853242961_loja_pods';
$user = 'u853242961_pods_salu';
$pass = 'Lucastav8012@';

echo "<h2>🔍 Diagnóstico - Problema com Tabela 'key'</h2>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ <strong>Conexão com banco estabelecida!</strong></p>";
    
    // 1. Verificar se tabela produtos existe
    echo "<h3>1. Verificação da Tabela 'produtos'</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'produtos'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabela 'produtos' existe!</p>";
        
        // Mostrar estrutura
        echo "<h4>Estrutura da tabela:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        $stmt = $pdo->query('DESCRIBE produtos');
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td><strong>{$row['Field']}</strong></td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar se coluna caracteristicas existe
        $stmt = $pdo->query("SHOW COLUMNS FROM produtos LIKE 'caracteristicas'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Coluna 'caracteristicas' existe!</p>";
        } else {
            echo "<p style='color: red;'>❌ Coluna 'caracteristicas' NÃO existe!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Tabela 'produtos' NÃO existe!</p>";
    }
    
    // 2. Verificar se existe tabela 'key'
    echo "<h3>2. Verificação da Tabela 'key'</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'key'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠️ Tabela 'key' existe! (Isso pode ser o problema)</p>";
        
        // Mostrar estrutura da tabela key
        echo "<h4>Estrutura da tabela 'key':</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        $stmt = $pdo->query('DESCRIBE `key`');
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td><strong>{$row['Field']}</strong></td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: green;'>✅ Tabela 'key' NÃO existe (correto)</p>";
    }
    
    // 3. Listar todas as tabelas
    echo "<h3>3. Todas as Tabelas no Banco</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    echo "<ul>";
    while ($row = $stmt->fetch()) {
        echo "<li>{$row[0]}</li>";
    }
    echo "</ul>";
    
    // 4. Teste de inserção de produto
    echo "<h3>4. Teste de Inserção de Produto</h3>";
    try {
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, preco, estoque, ativo, caracteristicas) VALUES (?, ?, ?, ?, ?)");
        $caracteristicas = json_encode(['teste' => 'valor']);
        $result = $stmt->execute(['Produto Teste', 10.00, 1, 1, $caracteristicas]);
        
        if ($result) {
            $id = $pdo->lastInsertId();
            echo "<p style='color: green;'>✅ Produto de teste inserido com ID: $id</p>";
            
            // Deletar o produto de teste
            $pdo->prepare("DELETE FROM produtos WHERE id = ?")->execute([$id]);
            echo "<p style='color: green;'>✅ Produto de teste removido</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao inserir produto de teste: " . $e->getMessage() . "</p>";
    }
    
    // 5. Verificar logs de erro do MySQL
    echo "<h3>5. Verificação de Logs</h3>";
    try {
        $stmt = $pdo->query("SHOW VARIABLES LIKE 'log_error'");
        $log = $stmt->fetch();
        if ($log) {
            echo "<p><strong>Log de erro:</strong> {$log['Value']}</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Não foi possível verificar logs: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Erro de conexão:</strong> " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Erro geral:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>💡 Possíveis Soluções</h3>";
echo "<ul>";
echo "<li>Se a tabela 'key' existe, considere renomeá-la ou removê-la</li>";
echo "<li>Verifique se há conflitos de nomes de tabelas</li>";
echo "<li>Execute o script de criação de tabelas novamente</li>";
echo "<li>Verifique as permissões do usuário do banco</li>";
echo "</ul>";

echo "<p><strong>Arquivo:</strong> diagnostico_produtos.php</p>";
echo "<p><strong>Você pode apagar este arquivo após usar.</strong></p>";
?>

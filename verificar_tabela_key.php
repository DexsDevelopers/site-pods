<?php
// Script para verificar problema com tabela 'key'
// Usando credenciais diretas da Hostinger

$host = 'localhost';
$db = 'u853242961_loja_pods';
$user = 'u853242961_pods_salu';
$pass = 'Lucastav8012@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão com banco estabelecida!\n\n";
    
    // Verificar se tabela 'produtos' existe
    echo "=== VERIFICANDO TABELA 'produtos' ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'produtos'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'produtos' existe!\n";
        
        // Mostrar estrutura
        echo "\nEstrutura da tabela 'produtos':\n";
        $stmt = $pdo->query('DESCRIBE produtos');
        while ($row = $stmt->fetch()) {
            echo "- {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
        }
    } else {
        echo "❌ Tabela 'produtos' NÃO existe!\n";
    }
    
    // Verificar se tabela 'key' existe
    echo "\n=== VERIFICANDO TABELA 'key' ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'key'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'key' existe!\n";
        
        // Mostrar estrutura
        echo "\nEstrutura da tabela 'key':\n";
        $stmt = $pdo->query('DESCRIBE `key`');
        while ($row = $stmt->fetch()) {
            echo "- {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
        }
    } else {
        echo "❌ Tabela 'key' NÃO existe!\n";
    }
    
    // Listar todas as tabelas
    echo "\n=== TODAS AS TABELAS NO BANCO ===\n";
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch()) {
        echo "- {$row[0]}\n";
    }
    
    // Verificar se há algum erro relacionado a 'key' nos logs
    echo "\n=== VERIFICANDO LOGS DE ERRO ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE '%log%'");
    if ($stmt->rowCount() > 0) {
        echo "Tabelas de log encontradas:\n";
        while ($row = $stmt->fetch()) {
            echo "- {$row[0]}\n";
        }
    } else {
        echo "Nenhuma tabela de log encontrada.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
}
?>

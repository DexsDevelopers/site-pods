<?php
// Script para verificar se a coluna 'caracteristicas' existe na tabela produtos
// Usando credenciais diretas da Hostinger

$host = 'localhost';
$db = 'u853242961_loja_pods';
$user = 'u853242961_pods_salu';
$pass = 'Lucastav8012@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão com banco estabelecida!\n\n";
    
    // Verificar estrutura da tabela produtos
    echo "=== ESTRUTURA DA TABELA 'produtos' ===\n";
    $stmt = $pdo->query('DESCRIBE produtos');
    $colunas = [];
    while ($row = $stmt->fetch()) {
        $colunas[] = $row['Field'];
        echo "- {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
    }
    
    // Verificar se coluna 'caracteristicas' existe
    echo "\n=== VERIFICANDO COLUNA 'caracteristicas' ===\n";
    if (in_array('caracteristicas', $colunas)) {
        echo "✅ Coluna 'caracteristicas' existe!\n";
    } else {
        echo "❌ Coluna 'caracteristicas' NÃO existe!\n";
        echo "Colunas disponíveis: " . implode(', ', $colunas) . "\n";
    }
    
    // Verificar se coluna 'key' existe (pode ser um problema de nome)
    echo "\n=== VERIFICANDO COLUNA 'key' ===\n";
    if (in_array('key', $colunas)) {
        echo "✅ Coluna 'key' existe!\n";
    } else {
        echo "❌ Coluna 'key' NÃO existe!\n";
    }
    
    // Tentar inserir um produto de teste para ver o erro exato
    echo "\n=== TESTE DE INSERÇÃO ===\n";
    try {
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, preco, estoque, ativo) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute(['Teste Produto', 10.00, 1, 1]);
        if ($result) {
            $id = $pdo->lastInsertId();
            echo "✅ Produto de teste inserido com ID: $id\n";
            
            // Deletar o produto de teste
            $pdo->prepare("DELETE FROM produtos WHERE id = ?")->execute([$id]);
            echo "✅ Produto de teste removido\n";
        }
    } catch (Exception $e) {
        echo "❌ Erro ao inserir produto de teste: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
}
?>

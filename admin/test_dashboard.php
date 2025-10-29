<?php
// Teste de diagnóstico do dashboard
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Teste de Diagnóstico do Dashboard</h2>";

// Teste 1: Verificar includes
echo "<h3>1. Testando includes...</h3>";
try {
    require_once '../includes/config_hostinger.php';
    echo "✅ config_hostinger.php carregado<br>";
} catch (Exception $e) {
    echo "❌ Erro ao carregar config_hostinger.php: " . $e->getMessage() . "<br>";
}

try {
    require_once '../includes/db.php';
    echo "✅ db.php carregado<br>";
} catch (Exception $e) {
    echo "❌ Erro ao carregar db.php: " . $e->getMessage() . "<br>";
}

// Teste 2: Verificar conexão com banco
echo "<h3>2. Testando conexão com banco...</h3>";
try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Conexão com banco estabelecida<br>";
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
}

// Teste 3: Verificar tabelas
echo "<h3>3. Testando tabelas...</h3>";
try {
    $tables = ['produtos', 'categorias', 'orders'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela '$table' existe<br>";
        } else {
            echo "❌ Tabela '$table' não existe<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar tabelas: " . $e->getMessage() . "<br>";
}

// Teste 4: Testar queries específicas
echo "<h3>4. Testando queries...</h3>";
try {
    // Teste query de produtos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE ativo = 1");
    $result = $stmt->fetch();
    echo "✅ Query produtos: " . $result['total'] . " produtos ativos<br>";
} catch (Exception $e) {
    echo "❌ Erro na query de produtos: " . $e->getMessage() . "<br>";
}

try {
    // Teste query de pedidos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $result = $stmt->fetch();
    echo "✅ Query pedidos: " . $result['total'] . " pedidos<br>";
} catch (Exception $e) {
    echo "❌ Erro na query de pedidos: " . $e->getMessage() . "<br>";
}

try {
    // Teste query de vendas
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status IN ('paid', 'delivered')");
    $result = $stmt->fetch();
    echo "✅ Query vendas hoje: R$ " . number_format($result['total'], 2, ',', '.') . "<br>";
} catch (Exception $e) {
    echo "❌ Erro na query de vendas: " . $e->getMessage() . "<br>";
}

echo "<h3>5. Teste concluído!</h3>";
?>

<?php
session_start();

// Simular admin logado
$_SESSION['admin_id'] = 1;
$_SESSION['admin_name'] = 'Admin';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<pre>";
echo "=== TESTE DE ADMIN ===\n\n";

// Teste 1: Incluir config
try {
    echo "1. Incluindo config.php...\n";
    include '../includes/config.php';
    echo "   ✅ OK\n";
} catch (Exception $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
}

// Teste 2: Incluir db.php
try {
    echo "2. Incluindo db.php...\n";
    include '../includes/db.php';
    echo "   ✅ OK\n";
} catch (Exception $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
}

// Teste 3: Conectar
try {
    echo "3. Conectando ao banco...\n";
    $conn = Database::getConnection();
    echo "   ✅ Conexão OK\n";
} catch (Exception $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
    echo "</pre>";
    exit;
}

// Teste 4: Query simples
try {
    echo "4. Testando query simples...\n";
    $stmt = $conn->prepare("SELECT 1");
    $stmt->execute();
    echo "   ✅ Query OK\n";
} catch (Exception $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
}

// Teste 5: Contar produtos
try {
    echo "5. Contando produtos...\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Produtos: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
}

// Teste 6: Contar categorias
try {
    echo "6. Contando categorias...\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM categories");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Categorias: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
}

// Teste 7: Listar páginas
echo "\n7. Verificando arquivos include do admin...\n";
$pages = ['dashboard', 'products', 'categories', 'orders', 'customers'];
foreach ($pages as $page) {
    $file = "includes/{$page}.php";
    if (file_exists($file)) {
        echo "   ✅ $file existe\n";
    } else {
        echo "   ❌ $file NÃO ENCONTRADO\n";
    }
}

// Teste 8: Simular inclusão do dashboard
echo "\n8. Testando inclusão do dashboard...\n";
try {
    ob_start();
    include 'includes/dashboard.php';
    $content = ob_get_clean();
    if (strlen($content) > 100) {
        echo "   ✅ Dashboard gerou " . strlen($content) . " bytes\n";
    } else {
        echo "   ⚠️  Dashboard gerou apenas " . strlen($content) . " bytes\n";
        echo "   Primeiros 200 caracteres:\n";
        echo "   " . substr(htmlspecialchars($content), 0, 200) . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
echo "</pre>";
?>

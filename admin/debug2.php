<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>DEBUG 2 - Testando Helpers</h1>";
echo "<hr>";

echo "<h2>Passo 1: Incluir config.php</h2>";
try {
    include '../includes/config.php';
    echo "✅ config.php OK<br>";
    echo "LOG_PATH = " . LOG_PATH . "<br>";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
    die;
}

echo "<h2>Passo 2: Incluir helpers.php</h2>";
try {
    include '../includes/helpers.php';
    echo "✅ helpers.php OK<br>";
    
    // Testar se função existe
    if (function_exists('logError')) {
        echo "✅ Função logError existe<br>";
    } else {
        echo "❌ Função logError NÃO existe<br>";
    }
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
    die;
}

echo "<h2>Passo 3: Incluir db.php</h2>";
try {
    include '../includes/db.php';
    echo "✅ db.php OK<br>";
    
    // Testar conexão
    $conn = Database::getConnection();
    echo "✅ Conexão OK<br>";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
    die;
}

echo "<h2>Passo 4: Testar query simples</h2>";
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Query OK - Produtos: " . $result['count'] . "<br>";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
}

echo "<h2>Passo 5: Testar se logError funciona</h2>";
try {
    logError("Teste de erro");
    echo "✅ logError() funcionou<br>";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>✅ Todos os passos OK! Problem não está aqui.</h2>";
echo "<p>Se chegar aqui, o problema está no dashboard.php ou index.php</p>";

?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_name'] = 'Admin';

echo "<h1>DEBUG - Testando Dashboard Direto</h1>";
echo "<hr>";

echo "<h2>1. Estado da Sessão:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>2. Testando Includes:</h2>";
try {
    include '../includes/config.php';
    echo "✅ config.php incluído<br>";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

try {
    include '../includes/db.php';
    echo "✅ db.php incluído<br>";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

try {
    include '../includes/helpers.php';
    echo "✅ helpers.php incluído<br>";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<h2>3. Conteúdo do Dashboard (RAW):</h2>";
echo "<pre>";
ob_start();
try {
    include 'includes/dashboard.php';
    $content = ob_get_clean();
    
    // Mostrar tamanho
    echo "Tamanho: " . strlen($content) . " bytes\n";
    echo "---\n";
    
    // Mostrar primeiras 500 caracteres
    echo "Primeiros 500 caracteres:\n";
    echo htmlspecialchars(substr($content, 0, 500));
    echo "\n...\n";
    
    // Mostrar quantas divs tem
    $div_count = substr_count($content, '<div');
    echo "\nTotal de <div>: $div_count\n";
    
    // Mostrar se tem erros visíveis
    if (strpos($content, 'Erro:') !== false) {
        echo "\n⚠️ AVISO: Encontrado texto 'Erro:' no conteúdo!\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
echo "</pre>";

echo "<h2>4. Dashboard Renderizado:</h2>";
echo "<div style='border: 2px solid red; padding: 20px; margin: 20px 0;'>";
try {
    include 'includes/dashboard.php';
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage();
}
echo "</div>";

?>

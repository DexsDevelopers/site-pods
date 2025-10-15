<?php
session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_name'] = 'Admin';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>TEST: Products.php Direct Include</h1>";
echo "<hr>";

echo "<h2>Testando inclusão direta:</h2>";

$cwd = getcwd();
echo "CWD: $cwd<br>";
echo "File exists includes/products.php: " . (file_exists('includes/products.php') ? 'SIM' : 'NÃO') . "<br>";

echo "<h2>Output buffer test:</h2>";
ob_start();

try {
    echo "ANTES DE INCLUIR<br>";
    include 'includes/products.php';
    echo "DEPOIS DE INCLUIR<br>";
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "<br>";
}

$content = ob_get_clean();

echo "Tamanho do content: " . strlen($content) . " bytes<br>";
echo "Content vazio? " . (empty($content) ? 'SIM' : 'NÃO') . "<br>";

echo "<h2>Content (primeiros 500 chars):</h2>";
echo "<pre>";
echo htmlspecialchars(substr($content, 0, 500));
echo "</pre>";

echo "<h2>Renderizado:</h2>";
echo $content;
?>

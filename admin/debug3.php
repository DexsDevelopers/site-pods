<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_name'] = 'Admin';

echo "<h1>DEBUG 3 - Simulando index.php</h1>";
echo "<hr>";

echo "<h2>Preparando ambiente...</h2>";

// Simular exatamente o que index.php faz
$page = $_GET['page'] ?? 'dashboard';
echo "Página requisitada: $page<br>";

// Testar se arquivo existe
$page_file = "includes/{$page}.php";
echo "Procurando: $page_file<br>";

if (file_exists($page_file)) {
    echo "✅ Arquivo existe!<br>";
} else {
    echo "❌ Arquivo NÃO EXISTE!<br>";
    die;
}

// Agora simular a inclusão
echo "<h2>Incluindo dashboard...</h2>";

// Abrir buffer
ob_start();

try {
    echo "<!-- Iniciando inclusão do dashboard -->\n";
    include $page_file;
    echo "<!-- Inclusão concluída -->\n";
} catch (Exception $e) {
    echo "<!-- ERRO durante inclusão: " . htmlspecialchars($e->getMessage()) . " -->\n";
}

$content = ob_get_clean();

echo "<h2>Resultado:</h2>";
echo "Tamanho do conteúdo: " . strlen($content) . " bytes<br>";

echo "<h2>Conteúdo (primeiros 1000 chars):</h2>";
echo "<pre>";
echo htmlspecialchars(substr($content, 0, 1000));
echo "</pre>";

echo "<h2>Conteúdo renderizado em caixa:</h2>";
echo "<div style='border: 3px solid red; padding: 20px; background: #f0f0f0; color: #000;'>";
echo $content;
echo "</div>";

?>

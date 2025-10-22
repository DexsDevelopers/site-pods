<?php
// =========================
// Wazzy Pods - Instalar Banco
// =========================
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Instalação Wazzy Pods - Banco de Dados</h2>";

$host = 'localhost';
$db   = 'u853242961_loja_pods';
$user = 'u853242961_pods_saluc';
$pass = 'Lucastav8012@';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p><strong>Conexão bem-sucedida!</strong></p>";
} catch (PDOException $e) {
    die("<p style='color: red;'>Erro na conexão: " . $e->getMessage() . "</p>");
}

$sql = file_get_contents('wazzypods-tabelas.sql');
if (!$sql) {
    die("<p style='color: red;'>Arquivo SQL não encontrado.</p>");
}

// Divide os statements
$statements = array_filter(array_map('trim', preg_split('/;\s*$/m', $sql)));
$ok = 0; $fail = 0;
foreach ($statements as $statement) {
    if (empty($statement)) continue;
    try {
        $pdo->exec($statement);
        echo "<span style='color: green;'>✓</span> <code>" . htmlentities(substr($statement, 0, 60)) . (strlen($statement) > 60 ? '...' : '') . "</code><br>";
        $ok++;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "<span style='color: gray;'>Tabela já existe</span>: <code>" . htmlentities(substr($statement, 0, 60)) . "...</code><br>";
        } else {
            echo "<span style='color: red;'>✗</span> Erro: " . $e->getMessage() . "<br>";
            $fail++;
        }
    }
}

echo "<br><strong>Finalizado!</strong> <span style='color: green;'>$ok OK</span> / <span style='color: red;'>$fail erro(s)</span><br>";
echo "<p><b>Você pode apagar este arquivo por segurança.</b></p>";

// Ver tabelas criadas
$res = $pdo->query('SHOW TABLES');
echo "<h4>Tabelas no banco:</h4><ul>";
foreach($res as $row) {
    echo "<li>".htmlentities(array_values($row)[0])."</li>";
}
echo "</ul>";

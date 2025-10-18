<?php
header('Content-Type: text/html; charset=utf-8');

// Configurações do banco (mesmo do criar_tabelas.php)
$host = 'localhost';
$db   = 'u853242961_loja_pods';
$user = 'u853242961_pods_saluc';
$pass = 'Lucastav8012@';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("<p style='color: red; font-size: 18px;'>Erro na conexão: " . $e->getMessage() . "</p>");
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Tabelas - Wazzy Pods</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: #e2e8f0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%); padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        .header h1 { color: white; margin: 0; font-size: 28px; }
        .header p { color: rgba(255,255,255,0.9); margin: 5px 0 0 0; }
        .table-card { background: #1e293b; border: 1px solid #334155; border-radius: 10px; margin-bottom: 20px; overflow: hidden; }
        .table-header { background: #0f172a; padding: 15px 20px; border-bottom: 2px solid #8b5cf6; }
        .table-header h2 { margin: 0; color: #a78bfa; font-size: 18px; display: flex; align-items: center; gap: 10px; }
        .table-info { padding: 10px 20px; background: #0f172a; border-bottom: 1px solid #334155; font-size: 12px; color: #94a3b8; }
        .table-content { padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f172a; padding: 12px 15px; text-align: left; color: #a78bfa; font-weight: 600; border-bottom: 1px solid #334155; font-size: 12px; }
        td { padding: 10px 15px; border-bottom: 1px solid #334155; font-size: 12px; }
        tr:hover { background: #1e293b; }
        .type-badge { display: inline-block; background: #334155; color: #a78bfa; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-family: monospace; }
        .key-badge { display: inline-block; background: #8b5cf6; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; margin-right: 5px; }
        .null-badge { display: inline-block; background: #ef4444; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; }
        .rows-count { display: inline-block; background: #10b981; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; }
        .summary { background: #1e293b; padding: 20px; border-radius: 10px; margin-top: 30px; border: 1px solid #334155; }
        .summary h3 { color: #a78bfa; margin-top: 0; }
        .summary p { margin: 5px 0; color: #cbd5e1; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-database"></i> Visualizar Tabelas - Wazzy Pods</h1>
            <p>Estrutura completa do banco de dados: <strong><?php echo htmlspecialchars($db); ?></strong></p>
        </div>

        <?php
        // Buscar todas as tabelas
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $totalTables = count($tables);
        $totalRows = 0;

        foreach ($tables as $table) {
            // Contar linhas da tabela
            $rowCount = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $totalRows += $rowCount;

            // Buscar informações das colunas
            $columns = $pdo->query("DESCRIBE `$table`")->fetchAll();

            echo '<div class="table-card">';
            echo '<div class="table-header">';
            echo '<h2><i class="fas fa-table"></i> ' . htmlspecialchars($table) . '</h2>';
            echo '</div>';
            echo '<div class="table-info">';
            echo '<strong>Registros:</strong> <span class="rows-count">' . number_format($rowCount) . '</span> | ';
            echo '<strong>Colunas:</strong> ' . count($columns) . '';
            echo '</div>';
            echo '<div class="table-content">';
            echo '<table>';
            echo '<thead><tr>';
            echo '<th>Campo</th>';
            echo '<th>Tipo</th>';
            echo '<th>Nulo?</th>';
            echo '<th>Chave</th>';
            echo '<th>Padrão</th>';
            echo '<th>Extra</th>';
            echo '</tr></thead><tbody>';

            foreach ($columns as $col) {
                echo '<tr>';
                echo '<td><strong>' . htmlspecialchars($col['Field']) . '</strong></td>';
                echo '<td><span class="type-badge">' . htmlspecialchars($col['Type']) . '</span></td>';
                echo '<td>';
                if ($col['Null'] === 'NO') {
                    echo '<span class="null-badge">NOT NULL</span>';
                } else {
                    echo '<span style="color: #94a3b8;">NULL</span>';
                }
                echo '</td>';
                echo '<td>';
                if ($col['Key']) {
                    if ($col['Key'] === 'PRI') echo '<span class="key-badge">PRIMARY</span>';
                    elseif ($col['Key'] === 'UNI') echo '<span class="key-badge">UNIQUE</span>';
                    elseif ($col['Key'] === 'MUL') echo '<span class="key-badge">FOREIGN</span>';
                    else echo $col['Key'];
                }
                echo '</td>';
                echo '<td><code style="color: #94a3b8; font-size: 11px;">' . htmlspecialchars($col['Default'] ?? '-') . '</code></td>';
                echo '<td><code style="color: #94a3b8; font-size: 11px;">' . htmlspecialchars($col['Extra'] ?? '-') . '</code></td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
            echo '</div>';
            echo '</div>';
        }
        ?>

        <!-- Resumo -->
        <div class="summary">
            <h3><i class="fas fa-chart-bar"></i> Resumo do Banco</h3>
            <p><strong>Banco de Dados:</strong> <?php echo htmlspecialchars($db); ?></p>
            <p><strong>Total de Tabelas:</strong> <strong style="color: #10b981;"><?php echo $totalTables; ?></strong></p>
            <p><strong>Total de Registros:</strong> <strong style="color: #10b981;"><?php echo number_format($totalRows); ?></strong></p>
            <p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #334155;"><em>✓ Este arquivo mostra a estrutura completa do seu banco. Você pode apagá-lo após usar.</em></p>
        </div>
    </div>
</body>
</html>

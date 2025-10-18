<?php
require_once __DIR__ . '/../includes/db.php';

echo "=================================\n";
echo "Instalando Sistema Admin Wazzy Pods\n";
echo "=================================\n\n";

try {
    // Ler o arquivo SQL
    $sqlFile = __DIR__ . '/../sql/admin_schema.sql';
    
    if (!file_exists($sqlFile)) {
        die("Erro: Arquivo SQL não encontrado!\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Dividir em statements
    $statements = array_filter(array_map('trim', preg_split('/;\s*$/m', $sql)));
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            $pdo->exec($statement);
            echo "✓ Executado: " . substr($statement, 0, 60) . "...\n";
            $success++;
        } catch (PDOException $e) {
            // Ignorar erros de "table already exists"
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "⚠ Tabela já existe: " . substr($statement, 0, 60) . "...\n";
            } else {
                echo "✗ Erro: " . $e->getMessage() . "\n";
                $errors++;
            }
        }
    }
    
    echo "\n=================================\n";
    echo "Instalação Concluída!\n";
    echo "✓ Sucesso: $success operações\n";
    if ($errors > 0) {
        echo "✗ Erros: $errors operações\n";
    }
    echo "=================================\n";
    
    // Verificar tabelas criadas
    echo "\nTabelas no banco de dados:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
} catch (Exception $e) {
    echo "Erro fatal: " . $e->getMessage() . "\n";
    exit(1);
}

<?php
/**
 * ========================================
 * SCRIPT INSTALADOR DO BANCO DE DADOS
 * ========================================
 * 
 * Executa o schema.sql automaticamente
 * e valida a instalação.
 * 
 * Acesso: /tools/install_schema.php
 */

// Carrega configurações
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Verificar se é POST para confirmar instalação
$install = $_POST['install'] ?? false;
$confirmed = $_POST['confirmed'] ?? false;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador de Schema - TechVapor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { 
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .alert {
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .alert.info {
            background: #e3f2fd;
            border-color: #2196f3;
            color: #1976d2;
        }
        .alert.success {
            background: #e8f5e9;
            border-color: #4caf50;
            color: #2e7d32;
        }
        .alert.error {
            background: #ffebee;
            border-color: #f44336;
            color: #c62828;
        }
        .alert.warning {
            background: #fff3e0;
            border-color: #ff9800;
            color: #e65100;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background: #f5f5f5;
            font-weight: 600;
            color: #333;
        }
        .table tr:hover {
            background: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-success {
            background: #c8e6c9;
            color: #2e7d32;
        }
        .status-error {
            background: #ffcdd2;
            color: #c62828;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .button.primary {
            background: #667eea;
            color: white;
        }
        .button.primary:hover {
            background: #5568d3;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .button.secondary {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        .button.secondary:hover {
            background: #efefef;
        }
        .button.danger {
            background: #f44336;
            color: white;
        }
        .button.danger:hover {
            background: #d32f2f;
        }
        .button-group {
            text-align: center;
            margin: 30px 0;
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: flex;
            align-items: center;
            font-size: 1rem;
            color: #333;
            cursor: pointer;
        }
        input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalador de Schema - TechVapor</h1>
        <p class="subtitle">Instale as tabelas do banco de dados automaticamente</p>

        <?php
        // Se for solicitação de instalação
        if ($install && $confirmed) {
            echo '<div class="alert info">⏳ Executando instalação...</div>';
            
            try {
                // Lê o arquivo schema.sql
                $schemaFile = __DIR__ . '/../sql/schema.sql';
                if (!file_exists($schemaFile)) {
                    throw new Exception("Arquivo schema.sql não encontrado em: $schemaFile");
                }
                
                $sql = file_get_contents($schemaFile);
                
                // Divide as queries por ponto e vírgula
                $queries = array_filter(array_map('trim', explode(';', $sql)));
                $pdo = Database::getConnection();
                
                $successCount = 0;
                $errors = [];
                
                foreach ($queries as $query) {
                    if (empty($query) || strpos($query, '--') === 0) continue;
                    
                    try {
                        $pdo->exec($query);
                        $successCount++;
                    } catch (PDOException $e) {
                        $errors[] = $e->getMessage();
                    }
                }
                
                echo '<div class="alert success">✅ Instalação concluída com sucesso!</div>';
                echo '<p>Total de queries executadas: <strong>' . $successCount . '</strong></p>';
                
                if (!empty($errors)) {
                    echo '<div class="alert warning">⚠️ Aviso: ' . count($errors) . ' erro(s) encontrado(s) (possível: tabelas já existem)</div>';
                }
                
                // Verificar tabelas criadas
                echo '<h2 style="margin-top: 30px; margin-bottom: 20px;">📊 Tabelas Criadas</h2>';
                
                $tables = $pdo->query("SHOW TABLES FROM " . DB_NAME)->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($tables)) {
                    echo '<table class="table">';
                    echo '<thead><tr><th>Tabela</th><th>Status</th></tr></thead><tbody>';
                    foreach ($tables as $table) {
                        echo '<tr>';
                        echo '<td><code>' . htmlspecialchars($table) . '</code></td>';
                        echo '<td><span class="status-badge status-success">✓ Criada</span></td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo '<div class="alert error">❌ Nenhuma tabela foi criada!</div>';
                }
                
                echo '<div class="button-group">';
                echo '<a href="test_connection.php" class="button secondary">Voltar ao Teste</a>';
                echo '</div>';
                
                logInfo("Schema instalado com sucesso. Total de queries: " . $successCount, "INSTALLER");
                
            } catch (Exception $e) {
                echo '<div class="alert error">❌ Erro na instalação: ' . htmlspecialchars($e->getMessage()) . '</div>';
                logError("Erro ao instalar schema: " . $e->getMessage(), "INSTALLER");
            }
        } else {
            // Verificar status atual
            try {
                $pdo = Database::getConnection();
                $tables = $pdo->query("SHOW TABLES FROM " . DB_NAME)->fetchAll(PDO::FETCH_COLUMN);
                $hasExistingTables = !empty($tables);
                
                if ($hasExistingTables) {
                    echo '<div class="alert warning">⚠️ Seu banco de dados já contém ' . count($tables) . ' tabela(s). A instalação irá atualizar/recriar as estruturas.</div>';
                } else {
                    echo '<div class="alert info">ℹ️ Seu banco de dados está vazio. A instalação criará todas as tabelas necessárias.</div>';
                }
                
                echo '<h2 style="margin: 30px 0 20px 0;">O que será instalado?</h2>';
                echo '<ul style="line-height: 1.8; color: #555;">';
                echo '<li>✓ Tabela de <strong>Usuários</strong> (clientes e admin)</li>';
                echo '<li>✓ Tabela de <strong>Categorias</strong> de produtos</li>';
                echo '<li>✓ Tabela de <strong>Produtos</strong> com descrições e preços</li>';
                echo '<li>✓ Tabela de <strong>Imagens</strong> de produtos</li>';
                echo '<li>✓ Tabela de <strong>Carrinho</strong> de compras</li>';
                echo '<li>✓ Tabela de <strong>Pedidos</strong> e itens</li>';
                echo '<li>✓ Tabela de <strong>Endereços</strong> de entrega</li>';
                echo '<li>✓ Tabela de <strong>Logs</strong> de auditoria</li>';
                echo '<li>✓ Tabela de <strong>Configurações</strong> da aplicação</li>';
                echo '<li>✓ Tabela de <strong>Cupons</strong> e promoções</li>';
                echo '<li>✓ Tabela de <strong>Avaliações</strong> de produtos</li>';
                echo '</ul>';
                
                echo '<form method="POST" style="margin-top: 30px;">';
                echo '<div class="form-group">';
                echo '<label>';
                echo '<input type="checkbox" name="confirmed" value="1" required>';
                echo ' Confirmo que desejo instalar/atualizar o schema do banco de dados';
                echo '</label>';
                echo '</div>';
                echo '<div class="button-group">';
                echo '<button type="submit" name="install" value="1" class="button primary">🚀 Instalar Schema</button>';
                echo '<a href="test_connection.php" class="button secondary">Cancelar</a>';
                echo '</div>';
                echo '</form>';
                
            } catch (Exception $e) {
                echo '<div class="alert error">❌ Erro ao conectar: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
        ?>
    </div>
</body>
</html>

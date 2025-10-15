<?php
/**
 * ========================================
 * SCRIPT DE CONFIGURAÇÃO DE DIRETÓRIOS
 * ========================================
 * 
 * Cria os diretórios faltantes automaticamente
 * 
 * Acesso: /tools/setup_directories.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$results = [];
$errors = [];
$projectRoot = dirname(dirname(__FILE__));

// Lista de diretórios necessários
$directories = [
    'admin' => 'Painel administrativo',
    'api' => 'Endpoints REST/JSON',
    'templates' => 'Componentes reutilizáveis',
    'pages' => 'Páginas públicas',
    'assets' => 'Recursos estáticos',
    'assets/css' => 'Estilos CSS',
    'assets/js' => 'Scripts JavaScript',
    'logs' => 'Logs da aplicação',
    'uploads' => 'Uploads de usuários',
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup de Diretórios - TechVapor</title>
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
            margin-bottom: 30px;
            text-align: center;
        }
        .alert {
            padding: 15px;
            margin: 15px 0;
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
        .item {
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .item.success {
            background: #f0f9ff;
            border-left: 4px solid #4caf50;
        }
        .item.error {
            background: #fff5f5;
            border-left: 4px solid #f44336;
        }
        .icon {
            font-size: 20px;
            min-width: 30px;
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
            text-align: center;
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
        .button-group {
            text-align: center;
            margin-top: 30px;
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
        <h1>🛠️ Setup de Diretórios - TechVapor</h1>

        <?php
        echo '<div class="alert info">⚙️ Criando diretórios necessários...</div>';
        
        $createdCount = 0;
        $skippedCount = 0;
        
        foreach ($directories as $dir => $description) {
            $fullPath = $projectRoot . DIRECTORY_SEPARATOR . $dir;
            $displayPath = str_replace($projectRoot, '.', $fullPath);
            
            if (is_dir($fullPath)) {
                echo '<div class="item success">';
                echo '<span class="icon">✓</span>';
                echo '<div>';
                echo '<strong>' . htmlspecialchars($dir) . '</strong> - ' . $description;
                echo '<br><small style="color: #666;">' . $displayPath . '</small>';
                echo '</div>';
                echo '</div>';
                $skippedCount++;
            } else {
                try {
                    if (mkdir($fullPath, 0755, true)) {
                        echo '<div class="item success">';
                        echo '<span class="icon">✓</span>';
                        echo '<div>';
                        echo '<strong>' . htmlspecialchars($dir) . '</strong> - ' . $description . ' (criado)';
                        echo '<br><small style="color: #666;">' . $displayPath . '</small>';
                        echo '</div>';
                        echo '</div>';
                        $createdCount++;
                    } else {
                        throw new Exception('Falha ao criar diretório');
                    }
                } catch (Exception $e) {
                    echo '<div class="item error">';
                    echo '<span class="icon">✗</span>';
                    echo '<div>';
                    echo '<strong>' . htmlspecialchars($dir) . '</strong> - Erro: ' . $e->getMessage();
                    echo '<br><small style="color: #666;">' . $displayPath . '</small>';
                    echo '</div>';
                    echo '</div>';
                    $errors[] = $dir;
                }
            }
        }
        
        echo '<div style="margin-top: 30px; padding: 20px; background: #f5f5f5; border-radius: 8px;">';
        echo '<strong>Resumo:</strong><br>';
        echo '✓ Diretórios OK: ' . ($skippedCount + $createdCount) . '<br>';
        echo '✓ Novos criados: ' . $createdCount . '<br>';
        echo '✗ Erros: ' . count($errors);
        echo '</div>';
        
        if (count($errors) > 0) {
            echo '<div class="alert error">';
            echo '❌ Alguns diretórios não puderam ser criados. Entre em contato com o suporte da hospedagem.';
            echo '</div>';
        } else {
            echo '<div class="alert success">';
            echo '✅ Todos os diretórios estão prontos! Você pode agora instalar o schema.';
            echo '</div>';
        }
        ?>

        <div class="button-group">
            <a href="test_connection.php" class="button secondary">← Voltar ao Teste</a>
            <a href="install_schema.php" class="button primary">→ Instalar Schema</a>
            <a href="../index.php" class="button secondary">🏠 Home</a>
        </div>
    </div>
</body>
</html>

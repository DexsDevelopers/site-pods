<?php
/**
 * ========================================
 * TESTE DE CONEXÃO COM BANCO DE DADOS
 * ========================================
 * 
 * Script para verificar se a configuração
 * do banco de dados está funcionando.
 * 
 * Acesso: /tools/test_connection.php
 */

// Carrega configurações
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Conexão - TechVapor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f0f2f5;
            padding: 40px 20px;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .test-item.success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        .test-item.error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        .test-item.warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        .icon {
            font-size: 20px;
            min-width: 30px;
        }
        .test-label {
            flex: 1;
        }
        .test-value {
            font-size: 0.85rem;
            opacity: 0.8;
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
        <h1>🔍 Teste de Configuração - TechVapor</h1>
        
        <?php
        $allPassed = true;
        
        // Teste 1: Arquivo .env
        echo '<div class="test-item ' . (file_exists(PROJECT_ROOT . '/.env') ? 'success' : 'error') . '">';
        echo file_exists(PROJECT_ROOT . '/.env') ? '✅' : '❌';
        echo ' <div class="test-label"><strong>Arquivo .env</strong><br><span class="test-value">Arquivo de configuração encontrado</span></div></div>';
        $allPassed = $allPassed && file_exists(PROJECT_ROOT . '/.env');
        
        // Teste 2: Diretórios essenciais
        $dirs = ['logs', 'uploads', 'includes', 'admin', 'api', 'templates'];
        foreach ($dirs as $dir) {
            $path = PROJECT_ROOT . '/' . $dir;
            $exists = is_dir($path);
            echo '<div class="test-item ' . ($exists ? 'success' : 'error') . '">';
            echo $exists ? '✅' : '❌';
            echo ' <div class="test-label"><strong>Diretório: ' . $dir . '</strong><br><span class="test-value">' . ($exists ? 'Encontrado' : 'Não encontrado') . '</span></div></div>';
            $allPassed = $allPassed && $exists;
        }
        
        // Teste 3: Conexão com Banco de Dados
        $dbConnected = false;
        $dbError = '';
        try {
            $dbConnected = Database::testConnection();
        } catch (Exception $e) {
            $dbError = $e->getMessage();
        }
        
        echo '<div class="test-item ' . ($dbConnected ? 'success' : 'error') . '">';
        echo $dbConnected ? '✅' : '❌';
        echo ' <div class="test-label"><strong>Banco de Dados</strong><br>';
        echo '<span class="test-value">';
        if ($dbConnected) {
            echo 'Conectado com sucesso a <code>' . DB_NAME . '</code>';
        } else {
            echo 'Erro: ' . $dbError;
        }
        echo '</span></div></div>';
        $allPassed = $allPassed && $dbConnected;
        
        // Teste 4: Configurações carregadas
        echo '<div class="test-item success">';
        echo '✅';
        echo ' <div class="test-label"><strong>Configurações da Aplicação</strong>';
        echo '<div style="font-size: 0.85rem; margin-top: 10px;">';
        echo '<code>APP_NAME:</code> ' . APP_NAME . '<br>';
        echo '<code>APP_ENV:</code> ' . APP_ENV . '<br>';
        echo '<code>DB_HOST:</code> ' . DB_HOST . '<br>';
        echo '<code>DB_NAME:</code> ' . DB_NAME . '<br>';
        echo '</div></div></div>';
        
        // Teste 5: Permissões de escrita
        $writableDirs = ['logs', 'uploads'];
        foreach ($writableDirs as $dir) {
            $path = PROJECT_ROOT . '/' . $dir;
            $writable = is_writable($path);
            echo '<div class="test-item ' . ($writable ? 'success' : 'warning') . '">';
            echo $writable ? '✅' : '⚠️';
            echo ' <div class="test-label"><strong>Escrita: ' . $dir . '</strong><br><span class="test-value">' . ($writable ? 'Diretório é gravável' : 'Aviso: Diretório pode não ser gravável') . '</span></div></div>';
        }
        
        // Resumo final
        echo '<hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">';
        echo '<div class="test-item ' . ($allPassed ? 'success' : 'error') . '">';
        echo $allPassed ? '✅ TUDO PRONTO!' : '❌ PROBLEMAS ENCONTRADOS';
        if ($allPassed) {
            echo '<br><span class="test-value">Sua aplicação está pronta para começar. Você pode removers este arquivo de teste por segurança.</span>';
        }
        echo '</div>';
        ?>
    </div>
</body>
</html>

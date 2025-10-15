<?php
/**
 * 🚨 EMERGENCY FIX - TechVapor
 * Ferramenta de emergência para corrigir problemas críticos
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$project_root = dirname(dirname(__FILE__));
$env_file = $project_root . '/.env';

// ========== SEÇÃO 1: CRIAR .ENV IMEDIATAMENTE ==========
$env_criado = false;
$erro_criacao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['force_create'])) {
    $env_content = "# TechVapor - Configurações de Banco de Dados
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u853242961_loja_pods
DB_USER=u853242961_pods_saluc
DB_PASSWORD=Lucastav8012@
DB_CHARSET=utf8mb4

# Configurações da Aplicação
APP_NAME=TechVapor
APP_ENV=production
APP_DEBUG=false
APP_URL=https://maroon-louse-320109.hostingersite.com

# Configurações de Sessão
SESSION_LIFETIME=3600
CSRF_TOKEN_LENGTH=32
HASH_ALGORITHM=bcrypt

# Configurações de Logs
LOG_LEVEL=info
LOG_PATH=logs/

# Configurações de Upload
UPLOAD_PATH=uploads/
UPLOAD_MAX_SIZE=5242880
";

    // Tentar remover arquivo antigo se existir
    if (file_exists($env_file)) {
        @unlink($env_file);
    }

    // Criar arquivo novo
    $bytes = @file_put_contents($env_file, $env_content);
    
    if ($bytes !== false) {
        $env_criado = true;
        // Aguardar e recarregar
        echo '<script>
            setTimeout(function() {
                location.href = location.href;
            }, 2000);
        </script>';
    } else {
        $erro_criacao = 'Erro ao escrever arquivo. Verifique permissões!';
    }
}

// ========== SEÇÃO 2: DIAGNÓSTICO COMPLETO ==========
$diagnostico = [];

// Verificar se .env existe
$diagnostico['env_existe'] = file_exists($env_file);
$diagnostico['env_size'] = $diagnostico['env_existe'] ? filesize($env_file) : 0;

// Ler conteúdo se existir
$env_conteudo = '';
if ($diagnostico['env_existe']) {
    $env_conteudo = file_get_contents($env_file);
}

// Verificar credenciais
$diagnostico['credenciais_ok'] = false;
if ($env_conteudo) {
    $tem_host = strpos($env_conteudo, 'DB_HOST=localhost') !== false;
    $tem_user = strpos($env_conteudo, 'DB_USER=u853242961_pods_saluc') !== false;
    $tem_pass = strpos($env_conteudo, 'DB_PASSWORD=Lucastav8012@') !== false;
    $tem_db = strpos($env_conteudo, 'DB_NAME=u853242961_loja_pods') !== false;
    
    $diagnostico['credenciais_ok'] = $tem_host && $tem_user && $tem_pass && $tem_db;
    $diagnostico['host_ok'] = $tem_host;
    $diagnostico['user_ok'] = $tem_user;
    $diagnostico['pass_ok'] = $tem_pass;
    $diagnostico['db_ok'] = $tem_db;
}

// Testar conexão
$diagnostico['conexao_testada'] = false;
$diagnostico['conexao_ok'] = false;
$diagnostico['conexao_erro'] = '';

if ($diagnostico['credenciais_ok']) {
    try {
        $dsn = 'mysql:host=localhost;port=3306;dbname=u853242961_loja_pods;charset=utf8mb4';
        $pdo = new PDO($dsn, 'u853242961_pods_saluc', 'Lucastav8012@', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $test = $pdo->query("SELECT 1");
        $diagnostico['conexao_testada'] = true;
        $diagnostico['conexao_ok'] = true;
    } catch (Exception $e) {
        $diagnostico['conexao_testada'] = true;
        $diagnostico['conexao_ok'] = false;
        $diagnostico['conexao_erro'] = $e->getMessage();
    }
}

// Verificar permissões
$diagnostico['dir_writable'] = is_writable($project_root);
$diagnostico['env_writable'] = file_exists($env_file) ? is_writable($env_file) : true;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 Emergency Fix - TechVapor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); }
        .glass { background: rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="text-slate-100 py-10 px-4">

    <div class="max-w-4xl mx-auto">
        <!-- Header CRÍTICO -->
        <div class="glass border-2 border-red-600 rounded-lg p-8 mb-8 bg-red-600/10">
            <h1 class="text-4xl font-black mb-2">
                <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>🚨 EMERGENCY FIX
            </h1>
            <p class="text-red-400 font-bold">Ferramenta de Emergência - TechVapor</p>
        </div>

        <!-- AÇÃO IMEDIATA -->
        <form method="POST" class="mb-8">
            <button type="submit" name="force_create" value="1" class="w-full py-6 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg font-black hover:shadow-2xl transition text-2xl border-2 border-red-400">
                <i class="fas fa-bolt mr-3"></i>🔴 FORÇAR CRIAÇÃO DO .env AGORA
            </button>
        </form>

        <?php if ($env_criado): ?>
        <div class="glass border-2 border-green-600 rounded-lg p-6 mb-8 bg-green-600/20">
            <p class="text-green-400 font-black text-lg">
                ✅ SUCESSO! Arquivo .env foi criado com credenciais CORRETAS!
            </p>
            <p class="text-green-300 mt-2">Recarregando página em alguns segundos...</p>
        </div>
        <?php endif; ?>

        <?php if ($erro_criacao): ?>
        <div class="glass border-2 border-red-600 rounded-lg p-6 mb-8 bg-red-600/20">
            <p class="text-red-400 font-black">
                ❌ ERRO: <?php echo $erro_criacao; ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- DIAGNOSTICO EM TEMPO REAL -->
        <div class="space-y-4">
            <!-- .env Status -->
            <div class="glass border border-purple-600/30 rounded-lg p-6">
                <h3 class="text-2xl font-black mb-4">📄 Status do .env</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-slate-800/50 p-4 rounded">
                        <p class="text-slate-400">Arquivo Existe</p>
                        <p class="text-2xl font-black"><?php echo $diagnostico['env_existe'] ? '✅ SIM' : '❌ NÃO'; ?></p>
                    </div>
                    <div class="bg-slate-800/50 p-4 rounded">
                        <p class="text-slate-400">Tamanho</p>
                        <p class="text-2xl font-black"><?php echo $diagnostico['env_size']; ?> bytes</p>
                    </div>
                </div>
            </div>

            <!-- Credenciais -->
            <div class="glass border border-purple-600/30 rounded-lg p-6">
                <h3 class="text-2xl font-black mb-4">🔑 Credenciais</h3>
                <div class="space-y-2 font-mono text-sm">
                    <div class="flex items-center justify-between">
                        <span>DB_HOST=localhost</span>
                        <span class="text-2xl"><?php echo isset($diagnostico['host_ok']) && $diagnostico['host_ok'] ? '✅' : '❌'; ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>DB_USER=u853242961_pods_saluc</span>
                        <span class="text-2xl"><?php echo isset($diagnostico['user_ok']) && $diagnostico['user_ok'] ? '✅' : '❌'; ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>DB_PASSWORD=Lucastav8012@</span>
                        <span class="text-2xl"><?php echo isset($diagnostico['pass_ok']) && $diagnostico['pass_ok'] ? '✅' : '❌'; ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>DB_NAME=u853242961_loja_pods</span>
                        <span class="text-2xl"><?php echo isset($diagnostico['db_ok']) && $diagnostico['db_ok'] ? '✅' : '❌'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Conexão -->
            <div class="glass border border-purple-600/30 rounded-lg p-6">
                <h3 class="text-2xl font-black mb-4">🔌 Conexão ao Banco</h3>
                <div class="bg-slate-800/50 p-4 rounded">
                    <?php if ($diagnostico['conexao_testada']): ?>
                        <?php if ($diagnostico['conexao_ok']): ?>
                            <p class="text-green-400 font-black">✅ Conexão OK!</p>
                        <?php else: ?>
                            <p class="text-red-400 font-black">❌ Erro na conexão:</p>
                            <p class="text-red-300 mt-2 text-sm"><?php echo htmlspecialchars($diagnostico['conexao_erro']); ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-yellow-400 font-black">⚠️ Não testado</p>
                        <p class="text-yellow-300 text-sm mt-2">Credenciais devem estar corretas para testar</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Permissões -->
            <div class="glass border border-purple-600/30 rounded-lg p-6">
                <h3 class="text-2xl font-black mb-4">🔐 Permissões</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span>Diretório Gravável</span>
                        <span class="text-2xl"><?php echo $diagnostico['dir_writable'] ? '✅' : '❌'; ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>.env Gravável</span>
                        <span class="text-2xl"><?php echo $diagnostico['env_writable'] ? '✅' : '❌'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Conteúdo Atual -->
            <?php if ($env_conteudo): ?>
            <div class="glass border border-purple-600/30 rounded-lg p-6">
                <h3 class="text-2xl font-black mb-4">📋 Conteúdo do .env</h3>
                <div class="bg-slate-900 rounded p-4 overflow-x-auto">
                    <pre class="text-xs text-slate-300 font-mono whitespace-pre-wrap"><?php echo htmlspecialchars($env_conteudo); ?></pre>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- PRÓXIMAS AÇÕES -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mt-8">
            <h3 class="text-2xl font-black mb-4">📋 Próximos Passos</h3>
            <ol class="space-y-3 text-slate-300">
                <li class="flex gap-3">
                    <span class="text-red-400 font-black">1.</span>
                    <span>Clique no botão VERMELHO acima</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-red-400 font-black">2.</span>
                    <span>Aguarde a confirmação de sucesso</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-red-400 font-black">3.</span>
                    <span>Verifique o status acima</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-red-400 font-black">4.</span>
                    <span>Acesse <a href="verify_database.php" class="text-purple-400 underline">Verificador BD</a></span>
                </li>
                <li class="flex gap-3">
                    <span class="text-red-400 font-black">5.</span>
                    <span>Vá para <a href="/" class="text-purple-400 underline">Home</a> ou <a href="../admin/" class="text-purple-400 underline">Admin</a></span>
                </li>
            </ol>
        </div>

        <!-- Footer -->
        <div class="text-center mt-12 text-slate-500">
            <p><i class="fas fa-cloud mr-2"></i>TechVapor Emergency Recovery</p>
        </div>
    </div>

</body>
</html>

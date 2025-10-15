<?php
/**
 * 🔧 TechVapor .env Fixer
 * Ferramenta para corrigir problemas com arquivo .env
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$project_root = dirname(dirname(__FILE__));
$env_file = $project_root . '/.env';

$mensagem = '';
$tipo_msg = '';
$env_conteudo = '';
$env_existe = file_exists($env_file);

// Se POST, criar/atualizar .env
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
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

    // Tentar escrever o arquivo
    if (@file_put_contents($env_file, $env_content)) {
        $mensagem = '✅ Arquivo .env criado/atualizado com SUCESSO!';
        $tipo_msg = 'success';
        $env_existe = true;
        $env_conteudo = $env_content;
        
        // Aguardar um pouco para aplicar
        sleep(1);
    } else {
        $mensagem = '❌ Erro ao criar/atualizar arquivo .env. Verifique permissões da pasta.';
        $tipo_msg = 'error';
    }
}

// Se GET e arquivo existe, mostrar conteúdo
if ($env_existe) {
    $env_conteudo = file_get_contents($env_file);
}

// Verificar credenciais no .env
$credenciais_corretas = false;
if ($env_existe) {
    $credenciais_corretas = strpos($env_conteudo, 'DB_USER=u853242961_pods_saluc') !== false 
                         && strpos($env_conteudo, 'DB_PASSWORD=Lucastav8012@') !== false;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corrigir .env - TechVapor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); }
        .glass { background: rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="text-slate-100 py-10 px-4">

    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mb-8">
            <h1 class="text-4xl font-black mb-2">
                <i class="fas fa-wrench text-purple-500 mr-3"></i>Corrigir .env
            </h1>
            <p class="text-slate-400">TechVapor - Ferramenta de Configuração</p>
        </div>

        <!-- Mensagem -->
        <?php if ($mensagem): ?>
        <div class="glass border border-purple-600/30 rounded-lg p-6 mb-8 <?php echo $tipo_msg === 'success' ? 'border-green-600' : 'border-red-600'; ?>">
            <p class="text-<?php echo $tipo_msg === 'success' ? 'green' : 'red'; ?>-400 font-bold">
                <?php echo $mensagem; ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Status -->
        <div class="grid md:grid-cols-2 gap-4 mb-8">
            <div class="glass border border-purple-600/30 rounded-lg p-6">
                <h3 class="font-black mb-2">Arquivo .env</h3>
                <div class="flex items-center gap-2">
                    <span class="text-2xl"><?php echo $env_existe ? '✅' : '❌'; ?></span>
                    <div>
                        <p class="font-bold"><?php echo $env_existe ? 'Existe' : 'Não encontrado'; ?></p>
                        <?php if ($env_existe): ?>
                            <p class="text-slate-400 text-sm"><?php echo strlen($env_conteudo); ?> bytes</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="glass border border-purple-600/30 rounded-lg p-6">
                <h3 class="font-black mb-2">Credenciais</h3>
                <div class="flex items-center gap-2">
                    <span class="text-2xl"><?php echo $credenciais_corretas ? '✅' : '❌'; ?></span>
                    <div>
                        <p class="font-bold"><?php echo $credenciais_corretas ? 'Corretas' : 'Incorretas'; ?></p>
                        <p class="text-slate-400 text-sm">
                            <?php echo $credenciais_corretas ? 'DB setup OK' : 'Precisa atualizar'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão Rápido -->
        <form method="POST" class="mb-8">
            <button type="submit" name="salvar" value="1" class="w-full py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg font-bold hover:shadow-lg transition text-lg">
                <i class="fas fa-check-circle mr-2"></i>🔧 Criar/Atualizar .env AGORA
            </button>
        </form>

        <!-- Informações -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mb-8">
            <h3 class="text-2xl font-black mb-4">
                <i class="fas fa-info-circle text-blue-400 mr-2"></i>Credenciais Corretas
            </h3>
            <div class="bg-slate-800/50 rounded p-4 font-mono text-sm space-y-1">
                <p><span class="text-green-400">DB_HOST=</span>localhost</p>
                <p><span class="text-green-400">DB_PORT=</span>3306</p>
                <p><span class="text-green-400">DB_NAME=</span>u853242961_loja_pods</p>
                <p><span class="text-green-400">DB_USER=</span>u853242961_pods_saluc</p>
                <p><span class="text-green-400">DB_PASSWORD=</span>Lucastav8012@</p>
            </div>
        </div>

        <!-- Conteúdo Atual -->
        <?php if ($env_existe): ?>
        <div class="glass border border-purple-600/30 rounded-lg p-8 mb-8">
            <h3 class="text-2xl font-black mb-4">
                <i class="fas fa-file-code text-yellow-400 mr-2"></i>Conteúdo Atual do .env
            </h3>
            <div class="bg-slate-900 rounded p-4 overflow-auto max-h-96">
                <pre class="text-xs text-slate-300 font-mono"><?php echo htmlspecialchars($env_conteudo); ?></pre>
            </div>
        </div>
        <?php endif; ?>

        <!-- Próximos Passos -->
        <div class="glass border border-purple-600/30 rounded-lg p-8">
            <h3 class="text-2xl font-black mb-4">
                <i class="fas fa-list-check text-purple-400 mr-2"></i>Próximos Passos
            </h3>
            <ol class="space-y-3 text-slate-300">
                <li class="flex gap-3">
                    <span class="text-purple-400 font-bold">1.</span>
                    <span>Clique no botão "Criar/Atualizar .env AGORA" acima</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-purple-400 font-bold">2.</span>
                    <span>Aguarde a mensagem de sucesso</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-purple-400 font-bold">3.</span>
                    <span>Acesse <a href="verify_database.php" class="text-purple-400 hover:text-purple-300 underline">Verificador de BD</a></span>
                </li>
                <li class="flex gap-3">
                    <span class="text-purple-400 font-bold">4.</span>
                    <span>Verifique se tabelas foram criadas</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-purple-400 font-bold">5.</span>
                    <span>Acesse a <a href="/" class="text-purple-400 hover:text-purple-300 underline">Home</a> ou <a href="../admin/login.php" class="text-purple-400 hover:text-purple-300 underline">Admin</a></span>
                </li>
            </ol>
        </div>

        <!-- Links Úteis -->
        <div class="flex gap-4 mt-8 flex-wrap">
            <a href="verify_database.php" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold hover:shadow-lg transition">
                <i class="fas fa-database mr-2"></i>Verificador BD
            </a>
            <a href="diagnose.php" class="px-6 py-3 bg-slate-700 text-white rounded-lg font-bold hover:bg-slate-600 transition">
                <i class="fas fa-stethoscope mr-2"></i>Diagnóstico
            </a>
            <a href="/" class="px-6 py-3 bg-slate-700 text-white rounded-lg font-bold hover:bg-slate-600 transition">
                <i class="fas fa-home mr-2"></i>Home
            </a>
            <button onclick="location.reload()" class="px-6 py-3 bg-slate-700 text-white rounded-lg font-bold hover:bg-slate-600 transition">
                <i class="fas fa-sync mr-2"></i>Recarregar
            </button>
        </div>

        <!-- Footer -->
        <div class="text-center mt-12 text-slate-500">
            <p><i class="fas fa-cloud mr-2"></i>TechVapor .env Fixer</p>
            <p class="text-xs mt-2">Ferramenta para corrigir problemas de configuração</p>
        </div>
    </div>

</body>
</html>

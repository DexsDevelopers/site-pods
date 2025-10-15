<?php
/**
 * 🔧 TechVapor Quick Setup
 * Ferramenta para criar arquivo .env automaticamente
 */

// Detectar raiz do projeto
$project_root = dirname(dirname(__FILE__));
$env_file = $project_root . '/.env';

// Variáveis padrão
$db_host = 'localhost';
$db_port = 3306;
$db_name = 'u853242961_loja_pods';
$db_user = 'u853242961_pods_saluc';
$db_password = 'Lucastav8012@';

// Processar formulário
$mensagem = '';
$tipo_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_port = $_POST['db_port'] ?? 3306;
    $db_name = $_POST['db_name'] ?? 'u853242961_loja_pods';
    $db_user = $_POST['db_user'] ?? 'u853242961_pods_saluc';
    $db_password = $_POST['db_password'] ?? '';
    
    $env_content = "# TechVapor - Configurações de Banco de Dados
DB_HOST=$db_host
DB_PORT=$db_port
DB_NAME=$db_name
DB_USER=$db_user
DB_PASSWORD=$db_password
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
LOG_LEVEL=warning
LOG_PATH=logs/

# Configurações de Upload
UPLOAD_PATH=uploads/
UPLOAD_MAX_SIZE=5242880
";

    if (file_put_contents($env_file, $env_content)) {
        $mensagem = '✅ Arquivo .env criado com sucesso!';
        $tipo_msg = 'success';
    } else {
        $mensagem = '❌ Erro ao criar arquivo .env. Verifique permissões da pasta.';
        $tipo_msg = 'error';
    }
}

$env_exists = file_exists($env_file);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Rápido - TechVapor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); }
        .glass { background: rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="text-slate-100 py-10 px-4">

    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mb-8">
            <h1 class="text-4xl font-black mb-2">
                <i class="fas fa-rocket text-purple-500 mr-3"></i>Setup Rápido
            </h1>
            <p class="text-slate-400">TechVapor - Configuração Inicial</p>
        </div>

        <!-- Status do .env -->
        <div class="glass border border-purple-600/30 rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Arquivo .env</h3>
                    <p class="text-slate-400 text-sm">Necessário para funcionar</p>
                </div>
                <div>
                    <?php if ($env_exists): ?>
                        <span class="px-4 py-2 bg-green-600/20 text-green-400 rounded-lg text-sm font-bold">
                            <i class="fas fa-check mr-2"></i>✅ Existe
                        </span>
                    <?php else: ?>
                        <span class="px-4 py-2 bg-red-600/20 text-red-400 rounded-lg text-sm font-bold">
                            <i class="fas fa-times mr-2"></i>❌ Não encontrado
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mensagem -->
        <?php if ($mensagem): ?>
        <div class="glass border border-purple-600/30 rounded-lg p-6 mb-8 <?php echo $tipo_msg === 'success' ? 'border-green-600' : 'border-red-600'; ?>">
            <p class="text-<?php echo $tipo_msg === 'success' ? 'green' : 'red'; ?>-400">
                <?php echo $mensagem; ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Formulário -->
        <div class="glass border border-purple-600/30 rounded-lg p-8">
            <h3 class="text-2xl font-black mb-6">📝 Configurações do Banco de Dados</h3>
            
            <form method="POST" class="space-y-4">
                <!-- DB_HOST -->
                <div>
                    <label class="block text-slate-300 mb-2">Host do Banco</label>
                    <input type="text" name="db_host" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" value="<?php echo htmlspecialchars($db_host); ?>">
                    <p class="text-slate-500 text-xs mt-1">Geralmente: localhost</p>
                </div>

                <!-- DB_PORT -->
                <div>
                    <label class="block text-slate-300 mb-2">Porta do MySQL</label>
                    <input type="number" name="db_port" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" value="<?php echo htmlspecialchars($db_port); ?>">
                    <p class="text-slate-500 text-xs mt-1">Geralmente: 3306</p>
                </div>

                <!-- DB_NAME -->
                <div>
                    <label class="block text-slate-300 mb-2">Nome do Banco de Dados</label>
                    <input type="text" name="db_name" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" value="<?php echo htmlspecialchars($db_name); ?>">
                    <p class="text-slate-500 text-xs mt-1">Exemplo: u853242961_loja_pods</p>
                </div>

                <!-- DB_USER -->
                <div>
                    <label class="block text-slate-300 mb-2">Usuário MySQL</label>
                    <input type="text" name="db_user" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" value="<?php echo htmlspecialchars($db_user); ?>">
                    <p class="text-slate-500 text-xs mt-1">Exemplo: u853242961_pods_saluc</p>
                </div>

                <!-- DB_PASSWORD -->
                <div>
                    <label class="block text-slate-300 mb-2">Senha MySQL</label>
                    <input type="password" name="db_password" class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" value="<?php echo htmlspecialchars($db_password); ?>">
                    <p class="text-slate-500 text-xs mt-1">Deixe em branco se não houver senha</p>
                </div>

                <!-- Botão -->
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold btn-hover ripple relative z-10 hover:shadow-lg transition">
                    <i class="fas fa-save mr-2"></i>Criar/Atualizar .env
                </button>
            </form>
        </div>

        <!-- Próximas Etapas -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mt-8">
            <h3 class="text-2xl font-black mb-4">📋 Próximos Passos</h3>
            <ol class="space-y-3 text-slate-300">
                <li class="flex gap-3">
                    <span class="text-purple-400 font-bold">1.</span>
                    <span>Preencha os dados de conexão do banco acima</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-purple-400 font-bold">2.</span>
                    <span>Clique em "Criar/Atualizar .env"</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-purple-400 font-bold">3.</span>
                    <span>Acesse o <a href="verify_database.php" class="text-purple-400 hover:text-purple-300">Verificador de BD</a></span>
                </li>
                <li class="flex gap-3">
                    <span class="text-purple-400 font-bold">4.</span>
                    <span>As tabelas serão criadas automaticamente</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-purple-400 font-bold">5.</span>
                    <span>Acesse <a href="/" class="text-purple-400 hover:text-purple-300">home</a> ou <a href="../admin/login.php" class="text-purple-400 hover:text-purple-300">admin</a></span>
                </li>
            </ol>
        </div>

        <!-- Info -->
        <div class="text-center mt-12 text-slate-500">
            <p><i class="fas fa-cloud mr-2"></i>TechVapor Setup Tool</p>
            <p class="text-xs mt-2">Se tiver dúvidas sobre as credenciais, verifique seu cPanel</p>
        </div>
    </div>

</body>
</html>

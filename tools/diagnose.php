<?php
/**
 * 🔍 TechVapor Diagnóstico
 * Ferramenta para diagnosticar problemas no servidor
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$diagnostico = [];

// 1. Verificar PHP Version
$diagnostico['php_version'] = [
    'titulo' => 'Versão do PHP',
    'valor' => phpversion(),
    'esperado' => '7.4+',
    'ok' => version_compare(phpversion(), '7.4.0', '>=')
];

// 2. Verificar extensões necessárias
$extensoes_necessarias = ['pdo', 'pdo_mysql', 'json', 'curl'];
$diagnostico['extensoes'] = [
    'titulo' => 'Extensões PHP',
    'esperado' => implode(', ', $extensoes_necessarias),
    'valor' => [],
    'ok' => true
];

foreach ($extensoes_necessarias as $ext) {
    $tem_ext = extension_loaded($ext);
    $diagnostico['extensoes']['valor'][$ext] = $tem_ext ? '✅' : '❌';
    if (!$tem_ext) {
        $diagnostico['extensoes']['ok'] = false;
    }
}

// 3. Verificar permissões
$dirs_verificar = [
    'logs/',
    'uploads/',
    'admin/',
    'api/',
    'tools/',
    'includes/'
];

$diagnostico['permissoes'] = [
    'titulo' => 'Permissões de Diretórios',
    'valor' => [],
    'ok' => true
];

$project_root = dirname(dirname(__FILE__));
foreach ($dirs_verificar as $dir) {
    $path = $project_root . '/' . $dir;
    $existe = file_exists($path);
    $é_dir = is_dir($path);
    $leitura = is_readable($path);
    $escrita = is_writable($path);
    
    $status = '✅' . ($existe ? ' existe' : ' NÃO existe');
    if (!$existe || !$é_dir || !$leitura) {
        $status = '❌ problema';
        $diagnostico['permissoes']['ok'] = false;
    }
    
    $diagnostico['permissoes']['valor'][$dir] = $status;
}

// 4. Verificar arquivo .env
$env_file = $project_root . '/.env';
$diagnostico['env_file'] = [
    'titulo' => 'Arquivo .env',
    'existe' => file_exists($env_file),
    'ok' => file_exists($env_file)
];

if (file_exists($env_file)) {
    $env_content = file_get_contents($env_file);
    $diagnostico['env_file']['size'] = strlen($env_content) . ' bytes';
}

// 5. Verificar config.php
$config_file = $project_root . '/includes/config.php';
$diagnostico['config_file'] = [
    'titulo' => 'Arquivo config.php',
    'existe' => file_exists($config_file),
    'ok' => file_exists($config_file)
];

// 6. Verificar db.php
$db_file = $project_root . '/includes/db.php';
$diagnostico['db_file'] = [
    'titulo' => 'Arquivo db.php',
    'existe' => file_exists($db_file),
    'ok' => file_exists($db_file)
];

// 7. Tenta conectar ao banco
$diagnostico['conexao_bd'] = [
    'titulo' => 'Conexão ao Banco de Dados',
    'ok' => false,
    'valor' => '❌ Não testado'
];

try {
    if (file_exists($config_file) && file_exists($db_file)) {
        include $config_file;
        include $db_file;
        
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $test = $conn->query("SELECT 1");
        
        $diagnostico['conexao_bd']['ok'] = true;
        $diagnostico['conexao_bd']['valor'] = '✅ Conectado';
        $diagnostico['conexao_bd']['database'] = DB_NAME;
    }
} catch (Exception $e) {
    $diagnostico['conexao_bd']['valor'] = '❌ ' . $e->getMessage();
}

// 8. Verificar logs
$log_dir = $project_root . '/logs/';
$diagnostico['logs'] = [
    'titulo' => 'Diretório de Logs',
    'existe' => is_dir($log_dir),
    'ok' => is_dir($log_dir) && is_writable($log_dir)
];

if (is_dir($log_dir)) {
    $files = glob($log_dir . '*.log');
    $diagnostico['logs']['arquivos'] = count($files);
}

// Calcular score geral
$total = 0;
$ok = 0;
foreach ($diagnostico as $item) {
    if (isset($item['ok'])) {
        $total++;
        if ($item['ok']) $ok++;
    }
}

$score = ($total > 0) ? round(($ok / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - TechVapor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); }
        .glass { background: rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="text-slate-100 py-10 px-4">

    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mb-8">
            <h1 class="text-4xl font-black mb-2">
                <i class="fas fa-stethoscope text-purple-500 mr-3"></i>Diagnóstico do Servidor
            </h1>
            <p class="text-slate-400">TechVapor - Verificação Completa</p>
        </div>

        <!-- Score Geral -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mb-8">
            <div class="text-center">
                <div class="text-6xl font-black <?php echo $score >= 80 ? 'text-green-400' : ($score >= 50 ? 'text-yellow-400' : 'text-red-400'); ?>">
                    <?php echo $score; ?>%
                </div>
                <p class="text-slate-400 mt-2">
                    <?php echo $ok; ?> de <?php echo $total; ?> itens OK
                </p>
                <p class="text-sm mt-2">
                    <?php
                    if ($score >= 80) {
                        echo '✅ Sistema OK - Procure o arquivo de logs para mais detalhes';
                    } elseif ($score >= 50) {
                        echo '⚠️ Alguns problemas detectados - Veja abaixo';
                    } else {
                        echo '❌ Múltiplos problemas - Ação necessária';
                    }
                    ?>
                </p>
            </div>
        </div>

        <!-- Detalhes -->
        <div class="space-y-4">
            <?php foreach ($diagnostico as $key => $item): ?>
            <div class="glass border border-purple-600/30 rounded-lg p-6 <?php echo (isset($item['ok']) && !$item['ok']) ? 'border-red-600' : ''; ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-lg"><?php echo $item['titulo']; ?></h3>
                        <?php if (isset($item['esperado'])): ?>
                            <p class="text-slate-400 text-sm">Esperado: <?php echo $item['esperado']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <?php if (isset($item['ok'])): ?>
                            <span class="text-2xl">
                                <?php echo $item['ok'] ? '✅' : '❌'; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (isset($item['valor'])): ?>
                    <div class="mt-4 text-sm">
                        <?php if (is_array($item['valor'])): ?>
                            <ul class="space-y-1">
                                <?php foreach ($item['valor'] as $k => $v): ?>
                                    <li><?php echo htmlspecialchars($k) . ': ' . htmlspecialchars($v); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p><?php echo htmlspecialchars($item['valor']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($item['database'])): ?>
                    <p class="mt-3 text-green-400">Database: <?php echo htmlspecialchars($item['database']); ?></p>
                <?php endif; ?>

                <?php if (isset($item['size'])): ?>
                    <p class="mt-3 text-slate-400">Tamanho: <?php echo htmlspecialchars($item['size']); ?></p>
                <?php endif; ?>

                <?php if (isset($item['arquivos'])): ?>
                    <p class="mt-3 text-slate-400">Arquivos de log: <?php echo $item['arquivos']; ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Recomendações -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mt-8">
            <h3 class="text-2xl font-black mb-4">
                <i class="fas fa-lightbulb text-yellow-400 mr-2"></i>Recomendações
            </h3>
            
            <?php if (!$diagnostico['env_file']['ok']): ?>
            <p class="text-yellow-400 mb-4">
                ⚠️ Arquivo .env não encontrado. Acesse <a href="quick_setup.php" class="underline">quick_setup.php</a> para criar.
            </p>
            <?php endif; ?>

            <?php if (!$diagnostico['conexao_bd']['ok']): ?>
            <p class="text-red-400 mb-4">
                ❌ Erro na conexão com banco de dados. Verifique credenciais no arquivo .env.
            </p>
            <?php endif; ?>

            <?php if ($score >= 80): ?>
            <p class="text-green-400">
                ✅ Sistema está funcionando corretamente. Se tiver erros 500, verifique os logs em <code>/logs/</code>.
            </p>
            <?php endif; ?>
        </div>

        <!-- Ações -->
        <div class="flex gap-4 mt-8 flex-wrap">
            <a href="quick_setup.php" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold hover:shadow-lg transition">
                <i class="fas fa-cog mr-2"></i>Setup
            </a>
            <a href="verify_database.php" class="px-6 py-3 bg-slate-700 text-white rounded-lg font-bold hover:bg-slate-600 transition">
                <i class="fas fa-database mr-2"></i>Verificador BD
            </a>
            <button onclick="location.reload()" class="px-6 py-3 bg-slate-700 text-white rounded-lg font-bold hover:bg-slate-600 transition">
                <i class="fas fa-sync mr-2"></i>Recarregar
            </button>
        </div>

        <!-- Footer -->
        <div class="text-center mt-12 text-slate-500">
            <p><i class="fas fa-cloud mr-2"></i>TechVapor Diagnostic Tool</p>
        </div>
    </div>

</body>
</html>

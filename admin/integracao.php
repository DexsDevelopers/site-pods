<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Valores padrão
$mercado_pago_public_key = '';
$mercado_pago_access_token = '';
$pagamento_ativo = false;
$message = '';
$error = '';

// Buscar configurações atuais
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT chave, valor FROM configuracoes WHERE chave LIKE 'mercado_pago_%' OR chave = 'pagamento_ativo'");
        $stmt->execute();
        $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $mercado_pago_public_key = $configs['mercado_pago_public_key'] ?? '';
        $mercado_pago_access_token = $configs['mercado_pago_access_token'] ?? '';
        $pagamento_ativo = ($configs['pagamento_ativo'] ?? 'false') === 'true';
    } catch (Exception $e) {
        $error = 'Erro ao buscar configurações: ' . $e->getMessage();
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $public_key = $_POST['mercado_pago_public_key'] ?? '';
    $access_token = $_POST['mercado_pago_access_token'] ?? '';
    $ativo = isset($_POST['pagamento_ativo']) ? 'true' : 'false';

    if (empty($public_key) || empty($access_token)) {
        $error = '❌ Preencha todas as chaves do Mercado Pago!';
    } else {
        try {
            // Atualizar ou inserir chaves
            $campos = ['mercado_pago_public_key', 'mercado_pago_access_token', 'pagamento_ativo'];
            $valores = [$public_key, $access_token, $ativo];

            foreach ($campos as $idx => $campo) {
                $checkStmt = $pdo->prepare("SELECT id FROM configuracoes WHERE chave = ?");
                $checkStmt->execute([$campo]);
                $exists = $checkStmt->fetch();

                if ($exists) {
                    $updateStmt = $pdo->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ?");
                    $updateStmt->execute([$valores[$idx], $campo]);
                } else {
                    $insertStmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor, tipo) VALUES (?, ?, 'texto')");
                    $insertStmt->execute([$campo, $valores[$idx]]);
                }
            }

            $message = '✅ Integração Mercado Pago salva com sucesso!';
            $mercado_pago_public_key = $public_key;
            $mercado_pago_access_token = $access_token;
            $pagamento_ativo = $ativo === 'true';
        } catch (Exception $e) {
            $error = '❌ Erro ao salvar: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrações - Wazzy Pods Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-900 text-slate-100">
    <!-- Header -->
    <header class="bg-slate-950 border-b border-purple-800/30 sticky top-0 z-40">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="/admin" class="text-2xl font-black gradient-text flex items-center gap-2">
                    <i class="fas fa-skull-crossbones"></i>
                    <span>Wazzy Pods</span>
                </a>
                <span class="text-slate-400">/ Integrações</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-slate-400">Olá, <?php echo $_SESSION['admin_nome'] ?? 'Admin'; ?></span>
                <a href="logout.php" class="btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </header>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-950 border-r border-purple-800/30">
            <nav class="p-4">
                <a href="/admin" class="nav-item">
                    <i class="fas fa-dashboard"></i> Dashboard
                </a>
                <a href="produtos.php" class="nav-item">
                    <i class="fas fa-box"></i> Produtos
                </a>
                <a href="categorias.php" class="nav-item">
                    <i class="fas fa-tags"></i> Categorias
                </a>
                <a href="pedidos.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
                <a href="clientes.php" class="nav-item">
                    <i class="fas fa-users"></i> Clientes
                </a>
                <a href="integracao.php" class="nav-item active">
                    <i class="fas fa-plug"></i> Integrações
                </a>
                <a href="configuracoes.php" class="nav-item">
                    <i class="fas fa-cog"></i> Configurações
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold gradient-text">Integrações</h1>
                <p class="text-slate-400 mt-2">Configure pagamentos e serviços externos</p>
            </div>

            <!-- Mensagens -->
            <?php if ($message): ?>
                <div class="mb-6 p-4 bg-green-900/20 border border-green-600 rounded-xl text-green-400">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-900/20 border border-red-600 rounded-xl text-red-400">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Mercado Pago Card -->
            <div class="bg-slate-800/50 rounded-xl p-8 backdrop-blur-sm border border-purple-800/30 mb-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-white text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white">Mercado Pago</h2>
                        <p class="text-slate-400">PIX, Cartão de Crédito e Boleto</p>
                    </div>
                    <div class="ml-auto">
                        <span class="px-4 py-2 rounded-full text-sm font-bold <?php echo $pagamento_ativo ? 'bg-green-900/30 text-green-400' : 'bg-red-900/30 text-red-400'; ?>">
                            <?php echo $pagamento_ativo ? '✅ Ativo' : '❌ Inativo'; ?>
                        </span>
                    </div>
                </div>

                <form method="POST" class="space-y-6">
                    <div class="p-4 bg-blue-900/20 border border-blue-800/50 rounded-lg">
                        <p class="text-blue-400 text-sm mb-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Como obter as chaves:</strong>
                        </p>
                        <ol class="text-slate-300 text-sm space-y-2 ml-6 list-decimal">
                            <li>Acesse <a href="https://www.mercadopago.com.br" target="_blank" class="text-blue-400 hover:underline">mercadopago.com.br</a></li>
                            <li>Faça login na sua conta de negócio</li>
                            <li>Vá em <strong>Configurações → Integrações → Credenciais</strong></li>
                            <li>Copie a <strong>Chave Pública</strong> e <strong>Token de Acesso</strong></li>
                            <li>Cole nos campos abaixo</li>
                        </ol>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Chave Pública (Public Key)</label>
                        <input type="text" name="mercado_pago_public_key" value="<?php echo htmlspecialchars($mercado_pago_public_key); ?>" 
                               placeholder="APP_USR-xxxxxxxxx..." required
                               class="w-full px-4 py-3 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-purple-600 font-mono text-sm">
                        <p class="text-slate-400 text-xs mt-2">Começa com APP_USR</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Token de Acesso (Access Token)</label>
                        <input type="text" name="mercado_pago_access_token" value="<?php echo htmlspecialchars($mercado_pago_access_token); ?>" 
                               placeholder="APP_USR-xxxxxxxxx..." required
                               class="w-full px-4 py-3 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-purple-600 font-mono text-sm">
                        <p class="text-slate-400 text-xs mt-2">Começa com APP_USR</p>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-slate-900/50 rounded-lg border border-purple-800/20">
                        <input type="checkbox" id="pagamento_ativo" name="pagamento_ativo" <?php echo $pagamento_ativo ? 'checked' : ''; ?> 
                               class="w-5 h-5 cursor-pointer">
                        <label for="pagamento_ativo" class="cursor-pointer flex-1">
                            <span class="font-bold">Ativar pagamentos</span>
                            <p class="text-slate-400 text-sm">Quando ativado, o checkout aparecerá na loja</p>
                        </label>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="btn-primary flex-1">
                            <i class="fas fa-save mr-2"></i> Salvar Integração
                        </button>
                        <a href="/admin" class="btn-secondary flex-1 text-center">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Próximas Integrações -->
            <div class="bg-slate-800/50 rounded-xl p-8 backdrop-blur-sm border border-purple-800/30">
                <h3 class="text-xl font-bold mb-6 text-slate-300">
                    <i class="fas fa-hourglass-end text-purple-400 mr-2"></i> Em Desenvolvimento
                </h3>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="p-4 bg-slate-900/50 rounded-lg border border-slate-700/50 opacity-50">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="fas fa-stripe text-slate-400 text-2xl"></i>
                            <h4 class="font-bold">Stripe</h4>
                        </div>
                        <p class="text-slate-400 text-sm">Cartão de crédito internacional</p>
                    </div>

                    <div class="p-4 bg-slate-900/50 rounded-lg border border-slate-700/50 opacity-50">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="fas fa-paypal text-slate-400 text-2xl"></i>
                            <h4 class="font-bold">PayPal</h4>
                        </div>
                        <p class="text-slate-400 text-sm">Pagamento via PayPal</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            color: #94a3b8;
            transition: all 0.3s;
        }
        
        .nav-item:hover {
            background: rgba(139, 92, 246, 0.1);
            color: #a78bfa;
        }
        
        .nav-item.active {
            background: rgba(139, 92, 246, 0.2);
            color: #a78bfa;
            border-left: 3px solid #a78bfa;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
        }

        .btn-secondary {
            background: rgba(139, 92, 246, 0.2);
            color: #a78bfa;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid rgba(139, 92, 246, 0.5);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            justify-content: center;
        }
        
        .btn-secondary:hover {
            background: rgba(139, 92, 246, 0.3);
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
    </style>
</body>
</html>

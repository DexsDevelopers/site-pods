<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Buscar configurações atuais
$sql = "SELECT chave, valor FROM configuracoes";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$configsArray = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Valores padrão
$configs = [
    'site_name' => $configsArray['site_name'] ?? 'Wazzy Pods',
    'site_email' => $configsArray['site_email'] ?? 'contato@wazzypods.com',
    'site_phone' => $configsArray['site_phone'] ?? '(11) 9999-9999',
    'site_address' => $configsArray['site_address'] ?? 'São Paulo, SP',
    'currency' => $configsArray['currency'] ?? 'BRL',
    'tax_rate' => $configsArray['tax_rate'] ?? '15',
    'shipping_fee' => $configsArray['shipping_fee'] ?? '10.00',
];

// Processar atualização
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($configs as $key => $oldValue) {
            $newValue = $_POST[$key] ?? '';
            
            // Verificar se a chave existe
            $checkStmt = $pdo->prepare("SELECT id FROM configuracoes WHERE chave = ?");
            $checkStmt->execute([$key]);
            $exists = $checkStmt->fetch();

            if ($exists) {
                // Atualizar
                $updateStmt = $pdo->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ?");
                $updateStmt->execute([$newValue, $key]);
            } else {
                // Inserir
                $insertStmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor, tipo) VALUES (?, ?, 'texto')");
                $insertStmt->execute([$key, $newValue]);
            }
        }
        $message = '✅ Configurações atualizadas com sucesso!';
        
        // Recarregar valores
        $stmt = $pdo->prepare("SELECT chave, valor FROM configuracoes");
        $stmt->execute();
        $configsArray = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $configs = array_merge($configs, $configsArray);
    } catch (Exception $e) {
        $error = '❌ Erro ao atualizar: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Wazzy Pods Admin</title>
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
                <span class="text-slate-400">/ Configurações</span>
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
                <a href="configuracoes.php" class="nav-item active">
                    <i class="fas fa-cog"></i> Configurações
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold gradient-text">Configurações do Sistema</h1>
                <p class="text-slate-400 mt-2">Gerencie as configurações gerais da loja</p>
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

            <!-- Formulário de Configurações -->
            <form method="POST" class="space-y-6">
                <!-- Informações da Loja -->
                <div class="bg-slate-800/50 rounded-xl p-6 backdrop-blur-sm border border-purple-800/30">
                    <h2 class="text-xl font-bold mb-6 text-purple-400">
                        <i class="fas fa-store mr-2"></i> Informações da Loja
                    </h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2">Nome da Loja</label>
                            <input type="text" name="site_name" value="<?php echo htmlspecialchars($configs['site_name']); ?>" 
                                   class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 focus:outline-none focus:border-purple-600">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Email de Contato</label>
                            <input type="email" name="site_email" value="<?php echo htmlspecialchars($configs['site_email']); ?>" 
                                   class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 focus:outline-none focus:border-purple-600">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Telefone</label>
                            <input type="tel" name="site_phone" value="<?php echo htmlspecialchars($configs['site_phone']); ?>" 
                                   class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 focus:outline-none focus:border-purple-600">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Endereço</label>
                            <input type="text" name="site_address" value="<?php echo htmlspecialchars($configs['site_address']); ?>" 
                                   class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 focus:outline-none focus:border-purple-600">
                        </div>
                    </div>
                </div>

                <!-- Configurações de Pagamento e Frete -->
                <div class="bg-slate-800/50 rounded-xl p-6 backdrop-blur-sm border border-purple-800/30">
                    <h2 class="text-xl font-bold mb-6 text-purple-400">
                        <i class="fas fa-money-bill-wave mr-2"></i> Pagamento e Frete
                    </h2>

                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2">Moeda</label>
                            <select name="currency" class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 focus:outline-none focus:border-purple-600">
                                <option value="BRL" <?php echo $configs['currency'] === 'BRL' ? 'selected' : ''; ?>>BRL - Real Brasileiro</option>
                                <option value="USD" <?php echo $configs['currency'] === 'USD' ? 'selected' : ''; ?>>USD - Dólar Americano</option>
                                <option value="EUR" <?php echo $configs['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Taxa de Imposto (%)</label>
                            <input type="number" name="tax_rate" value="<?php echo htmlspecialchars($configs['tax_rate']); ?>" 
                                   step="0.01" min="0" max="100"
                                   class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 focus:outline-none focus:border-purple-600">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Taxa de Frete (R$)</label>
                            <input type="number" name="shipping_fee" value="<?php echo htmlspecialchars($configs['shipping_fee']); ?>" 
                                   step="0.01" min="0"
                                   class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 focus:outline-none focus:border-purple-600">
                        </div>
                    </div>
                </div>

                <!-- Informações do Banco de Dados -->
                <div class="bg-slate-800/50 rounded-xl p-6 backdrop-blur-sm border border-purple-800/30">
                    <h2 class="text-xl font-bold mb-6 text-purple-400">
                        <i class="fas fa-database mr-2"></i> Informações do Sistema
                    </h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="p-4 bg-slate-900/50 rounded-lg border border-purple-800/20">
                            <p class="text-sm text-slate-400">Versão do PHP</p>
                            <p class="text-lg font-bold"><?php echo phpversion(); ?></p>
                        </div>

                        <div class="p-4 bg-slate-900/50 rounded-lg border border-purple-800/20">
                            <p class="text-sm text-slate-400">Data do Servidor</p>
                            <p class="text-lg font-bold"><?php echo date('d/m/Y H:i:s'); ?></p>
                        </div>

                        <div class="p-4 bg-slate-900/50 rounded-lg border border-purple-800/20">
                            <p class="text-sm text-slate-400">Banco de Dados</p>
                            <p class="text-lg font-bold">MySQL / PDO</p>
                        </div>

                        <div class="p-4 bg-slate-900/50 rounded-lg border border-purple-800/20">
                            <p class="text-sm text-slate-400">Ambiente</p>
                            <p class="text-lg font-bold"><?php echo getenv('APP_ENV') ?? 'Produção'; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Botão Salvar -->
                <div class="flex gap-4">
                    <button type="submit" class="btn-primary flex-1">
                        <i class="fas fa-save mr-2"></i> Salvar Configurações
                    </button>
                    <a href="/admin" class="btn-secondary flex-1 text-center">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </a>
                </div>
            </form>
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

<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

$error = '';
$message = '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'login';

// Processar Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $error = 'Preencha email e senha!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'customer'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($senha, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                header('Location: /');
                exit;
            } else {
                $error = 'Email ou senha incorretos!';
            }
        } catch (Exception $e) {
            $error = 'Erro ao fazer login: ' . $e->getMessage();
        }
    }
}

// Processar Registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'registro') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $error = 'Preencha todos os campos obrigatórios!';
    } elseif ($senha !== $confirmar_senha) {
        $error = 'As senhas não coincidem!';
    } elseif (strlen($senha) < 6) {
        $error = 'A senha deve ter no mínimo 6 caracteres!';
    } else {
        try {
            // Verificar se email já existe
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = 'Este email já está registrado!';
            } else {
                // Inserir novo usuário
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, status, created_at) VALUES (?, ?, ?, ?, 'customer', 'active', NOW())");
                $stmt->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $telefone]);

                $message = 'Cadastro realizado com sucesso! Faça login agora.';
                $tab = 'login';
            }
        } catch (Exception $e) {
            $error = 'Erro ao registrar: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Registro - Wazzy Pods</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100" style="background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 100%);">
    <!-- Header Simples -->
    <header class="bg-slate-950 border-b border-purple-800/30 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/" class="flex items-center gap-2 group">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                    <i class="fas fa-skull-crossbones text-white"></i>
                </div>
                <div class="font-black text-lg gradient-text">WAZZY PODS</div>
            </a>
            <a href="/" class="text-slate-400 hover:text-purple-400 transition">← Voltar para Loja</a>
        </div>
    </header>

    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full">
            <!-- Abas -->
            <div class="flex gap-2 mb-6">
                <a href="?tab=login" class="flex-1 py-3 px-4 rounded-lg font-bold text-center transition <?php echo $tab === 'login' ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white' : 'bg-slate-800/50 text-slate-400 hover:text-slate-200'; ?>">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
                <a href="?tab=registro" class="flex-1 py-3 px-4 rounded-lg font-bold text-center transition <?php echo $tab === 'registro' ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white' : 'bg-slate-800/50 text-slate-400 hover:text-slate-200'; ?>">
                    <i class="fas fa-user-plus mr-2"></i> Registro
                </a>
            </div>

            <!-- Mensagens -->
            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-900/20 border border-red-600 rounded-lg text-red-400">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="mb-4 p-4 bg-green-900/20 border border-green-600 rounded-lg text-green-400">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- LOGIN -->
            <?php if ($tab === 'login'): ?>
                <div class="glass rounded-xl p-8 border border-purple-800/30">
                    <h1 class="text-2xl font-bold mb-6 gradient-text">Bem-vindo!</h1>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="login">

                        <div>
                            <label class="block text-sm font-medium mb-2">Email</label>
                            <input type="email" name="email" required
                                   class="w-full px-4 py-3 bg-slate-800/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-purple-600">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Senha</label>
                            <input type="password" name="senha" required
                                   class="w-full px-4 py-3 bg-slate-800/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-purple-600">
                        </div>

                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-white hover:shadow-lg transition">
                            <i class="fas fa-sign-in-alt mr-2"></i> Entrar
                        </button>
                    </form>

                    <p class="text-center text-slate-400 mt-4 text-sm">
                        Não tem conta? <a href="?tab=registro" class="text-purple-400 hover:text-purple-300">Registre-se aqui</a>
                    </p>
                </div>
            <?php endif; ?>

            <!-- REGISTRO -->
            <?php if ($tab === 'registro'): ?>
                <div class="glass rounded-xl p-8 border border-purple-800/30">
                    <h1 class="text-2xl font-bold mb-6 gradient-text">Criar Conta</h1>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="registro">

                        <div>
                            <label class="block text-sm font-medium mb-2">Nome</label>
                            <input type="text" name="nome" required
                                   class="w-full px-4 py-3 bg-slate-800/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-purple-600">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Email</label>
                            <input type="email" name="email" required
                                   class="w-full px-4 py-3 bg-slate-800/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-purple-600">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Telefone (opcional)</label>
                            <input type="tel" name="telefone"
                                   class="w-full px-4 py-3 bg-slate-800/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-purple-600"
                                   placeholder="(11) 9999-9999">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Senha</label>
                            <input type="password" name="senha" required minlength="6"
                                   class="w-full px-4 py-3 bg-slate-800/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-purple-600"
                                   placeholder="Mínimo 6 caracteres">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Confirmar Senha</label>
                            <input type="password" name="confirmar_senha" required minlength="6"
                                   class="w-full px-4 py-3 bg-slate-800/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-purple-600">
                        </div>

                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-white hover:shadow-lg transition">
                            <i class="fas fa-user-plus mr-2"></i> Registrar
                        </button>
                    </form>

                    <p class="text-center text-slate-400 mt-4 text-sm">
                        Já tem conta? <a href="?tab=login" class="text-purple-400 hover:text-purple-300">Faça login</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

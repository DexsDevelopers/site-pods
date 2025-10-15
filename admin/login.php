<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Admin padrão (em produção, usar banco de dados)
    if ($email === 'admin@techvapor.com' && $password === 'admin123') {
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_name'] = 'Administrador';
        header('Location: index.php');
        exit;
    } else {
        $erro = 'Email ou senha inválidos!';
    }
}

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TechVapor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-slate-900/50 backdrop-blur border border-purple-600/30 rounded-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-black text-white mb-2">
                    <i class="fas fa-lock mr-2 text-purple-500"></i>Admin Panel
                </h1>
                <p class="text-slate-400">TechVapor Dashboard</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="bg-red-600/20 border border-red-600 text-red-400 px-4 py-3 rounded mb-6">
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-slate-300 mb-2">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="admin@techvapor.com">
                </div>

                <div>
                    <label class="block text-slate-300 mb-2">Senha</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="••••••••">
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded hover:shadow-lg transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Entrar
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-700">
                <p class="text-slate-400 text-sm text-center">
                    <strong>Demo:</strong><br>
                    Email: admin@techvapor.com<br>
                    Senha: admin123
                </p>
            </div>
        </div>
    </div>
</body>
</html>

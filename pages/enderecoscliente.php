<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se cliente está logado
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: /pages/login-cliente.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Processar novo endereço
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'adicionar') {
    $rua = $_POST['rua'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $principal = isset($_POST['principal']) ? 1 : 0;

    if (empty($rua) || empty($numero) || empty($bairro) || empty($cidade) || empty($estado) || empty($cep)) {
        $error = 'Preencha todos os campos obrigatórios!';
    } else {
        try {
            // Se é principal, desmarcar outros
            if ($principal) {
                $stmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
                $stmt->execute([$user_id]);
            }

            $stmt = $pdo->prepare("INSERT INTO addresses (user_id, street, number, complement, neighborhood, city, state, cep, is_default, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $rua, $numero, $complemento, $bairro, $cidade, $estado, $cep, $principal]);

            $message = '✅ Endereço adicionado com sucesso!';
        } catch (Exception $e) {
            $error = 'Erro ao adicionar endereço: ' . $e->getMessage();
        }
    }
}

// Deletar endereço
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deletar') {
    $endereco_id = $_POST['endereco_id'] ?? 0;
    try {
        $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$endereco_id, $user_id]);
        $message = '✅ Endereço removido!';
    } catch (Exception $e) {
        $error = 'Erro ao remover endereço';
    }
}

// Buscar endereços do cliente
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$user_id]);
$enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Endereços - Wazzy Pods</title>
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
    <?php include '../header.php'; ?>

    <div class="pt-32 pb-20 px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-5xl font-black mb-2"><span class="gradient-text">Meus Endereços</span></h1>
            <p class="text-slate-400 mb-8">Gerencie seus endereços de entrega</p>

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

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Formulário Adicionar -->
                <div class="lg:col-span-1">
                    <div class="glass rounded-xl p-6 border border-purple-800/30 sticky top-32">
                        <h2 class="text-xl font-bold mb-4 gradient-text">Novo Endereço</h2>

                        <form method="POST" class="space-y-3">
                            <input type="hidden" name="action" value="adicionar">

                            <div>
                                <label class="block text-sm font-medium mb-1">Rua *</label>
                                <input type="text" name="rua" required
                                       class="w-full px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Nº *</label>
                                    <input type="text" name="numero" required
                                           class="w-full px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium mb-1">Complemento</label>
                                    <input type="text" name="complemento"
                                           class="w-full px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Bairro *</label>
                                <input type="text" name="bairro" required
                                       class="w-full px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Cidade *</label>
                                    <input type="text" name="cidade" required
                                           class="w-full px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Estado *</label>
                                    <input type="text" name="estado" placeholder="SP" maxlength="2" required
                                           class="w-full px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">CEP *</label>
                                <input type="text" name="cep" placeholder="00000-000" required
                                       class="w-full px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                            </div>

                            <div class="flex items-center gap-2 pt-2">
                                <input type="checkbox" id="principal" name="principal" class="w-4 h-4">
                                <label for="principal" class="text-sm">Endereço padrão</label>
                            </div>

                            <button type="submit" class="w-full py-2 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-sm hover:shadow-lg transition">
                                <i class="fas fa-plus mr-1"></i> Adicionar
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista de Endereços -->
                <div class="lg:col-span-2">
                    <?php if (empty($enderecos)): ?>
                        <div class="glass rounded-xl p-12 text-center border border-purple-800/30">
                            <i class="fas fa-map-marker-alt text-6xl text-slate-600 mb-4"></i>
                            <h2 class="text-2xl font-bold mb-2">Nenhum endereço cadastrado</h2>
                            <p class="text-slate-400">Adicione um endereço para poder fazer compras</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($enderecos as $endereco): ?>
                                <div class="glass rounded-xl p-6 border border-purple-800/30 hover:border-purple-600/50 transition">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="font-bold text-lg">
                                                <?php echo htmlspecialchars($endereco['street'] . ', ' . $endereco['number']); ?>
                                            </h3>
                                            <?php if ($endereco['is_default']): ?>
                                                <span class="inline-block px-2 py-1 bg-purple-900/30 text-purple-400 text-xs rounded-full mt-1">
                                                    ⭐ Endereço padrão
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="deletar">
                                            <input type="hidden" name="endereco_id" value="<?php echo $endereco['id']; ?>">
                                            <button type="submit" class="text-red-400 hover:text-red-300" onclick="return confirm('Tem certeza?')">
                                                <i class="fas fa-trash text-lg"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="text-slate-300 space-y-1">
                                        <p><?php echo htmlspecialchars($endereco['street']); ?>, <?php echo htmlspecialchars($endereco['number']); ?></p>
                                        <?php if ($endereco['complement']): ?>
                                            <p>Complemento: <?php echo htmlspecialchars($endereco['complement']); ?></p>
                                        <?php endif; ?>
                                        <p><?php echo htmlspecialchars($endereco['neighborhood']); ?>, <?php echo htmlspecialchars($endereco['city']); ?> - <?php echo htmlspecialchars($endereco['state']); ?></p>
                                        <p class="font-mono text-purple-400">CEP: <?php echo htmlspecialchars($endereco['cep']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>

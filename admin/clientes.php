<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Buscar clientes
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = implode(' AND ', $where);

// Contar total
$countSql = "SELECT COUNT(*) as total FROM users WHERE $whereClause AND role = 'customer'";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Buscar clientes
$sql = "SELECT * FROM users 
        WHERE $whereClause AND role = 'customer'
        ORDER BY created_at DESC
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Clientes - Wazzy Pods Admin</title>
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
                <span class="text-slate-400">/ Clientes</span>
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
                <a href="clientes.php" class="nav-item active">
                    <i class="fas fa-users"></i> Clientes
                </a>
                <a href="configuracoes.php" class="nav-item">
                    <i class="fas fa-cog"></i> Configurações
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold gradient-text">Gerenciar Clientes</h1>
                <p class="text-slate-400 mt-2">Total de clientes: <strong><?php echo $total; ?></strong></p>
            </div>

            <!-- Filtros -->
            <div class="bg-slate-800/50 rounded-xl p-6 mb-6 backdrop-blur-sm border border-purple-800/30">
                <form method="GET" class="flex gap-4">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Buscar cliente por nome, email ou telefone..." 
                           class="flex-1 px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </form>
            </div>

            <!-- Tabela de Clientes -->
            <div class="bg-slate-800/50 rounded-xl overflow-hidden backdrop-blur-sm border border-purple-800/30">
                <table class="w-full">
                    <thead class="bg-slate-900/50 border-b border-purple-800/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Telefone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Data Cadastro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-800/20">
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fas fa-users text-4xl mb-4"></i>
                                    <p>Nenhum cliente encontrado</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr class="hover:bg-slate-900/30 transition">
                                    <td class="px-6 py-4 text-sm font-medium"><?php echo htmlspecialchars($cliente['name']); ?></td>
                                    <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($cliente['email']); ?></td>
                                    <td class="px-6 py-4 text-sm text-slate-400"><?php echo htmlspecialchars($cliente['phone'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-sm text-slate-400"><?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-lg text-xs <?php echo $cliente['status'] === 'active' ? 'bg-green-900/30 text-green-400' : 'bg-red-900/30 text-red-400'; ?>">
                                            <?php echo ucfirst($cliente['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button onclick="visualizarCliente(<?php echo $cliente['id']; ?>)" 
                                                    class="p-2 bg-blue-900/30 text-blue-400 rounded-lg hover:bg-blue-900/50 transition">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="deletarCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['name']); ?>')" 
                                                    class="p-2 bg-red-900/30 text-red-400 rounded-lg hover:bg-red-900/50 transition">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex justify-center gap-2">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-4 py-2 rounded-lg <?php echo $i == $page ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'; ?> transition">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
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
            padding: 0.5rem 1.5rem;
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

    <script>
        function visualizarCliente(id) {
            alert('Detalhes do cliente #' + id + ' (em desenvolvimento)');
        }

        function deletarCliente(id, nome) {
            Swal.fire({
                title: 'Tem certeza?',
                text: 'Deseja deletar o cliente "' + nome + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, deletar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/clientes.php', {
                        method: 'DELETE',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id: id})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Deletado!', 'Cliente removido com sucesso.', 'success')
                            .then(() => location.reload());
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>

<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Buscar produtos
$search = $_GET['search'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = '(p.nome LIKE ? OR p.descricao LIKE ? OR p.sku LIKE ?)';
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($categoria) {
    $where[] = 'p.categoria_id = ?';
    $params[] = $categoria;
}

$whereClause = implode(' AND ', $where);

// Contar total
$countSql = "SELECT COUNT(*) as total FROM produtos p WHERE $whereClause";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Buscar produtos com categoria
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE $whereClause 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

// Buscar categorias para o filtro
$categorias = $pdo->query("SELECT * FROM categorias WHERE ativo = 1 ORDER BY ordem, nome")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Wazzy Pods Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-900 text-slate-100">
    <!-- Header -->
    <header class="bg-slate-950 border-b border-purple-800/30 sticky top-0 z-40">
        <div class="px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2 md:gap-4">
                <button id="sidebarToggle" class="md:hidden text-xl text-slate-400 hover:text-purple-400 transition">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="/admin" class="text-lg md:text-2xl font-black gradient-text flex items-center gap-2">
                    <i class="fas fa-skull-crossbones"></i>
                    <span class="hidden sm:inline">Wazzy Pods</span>
                </a>
                <span class="text-xs md:text-sm text-slate-400 hidden sm:inline">/ Produtos</span>
            </div>
            <div class="flex items-center gap-2 md:gap-4">
                <span class="text-xs md:text-sm text-slate-400 hidden sm:inline">Olá, <?php echo $_SESSION['admin_nome'] ?? 'Admin'; ?></span>
                <a href="logout.php" class="btn-danger text-xs md:text-sm px-2 py-1 md:px-4 md:py-2">
                    <i class="fas fa-sign-out-alt"></i> <span class="hidden sm:inline">Sair</span>
                </a>
            </div>
        </div>
    </header>

    <div class="flex relative">
        <!-- Overlay para mobile -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 hidden md:hidden z-30"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed md:relative w-64 h-screen md:h-auto min-h-screen bg-slate-950 border-r border-purple-800/30 transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
            <nav class="p-4 space-y-2">
                <a href="/admin" class="nav-item">
                    <i class="fas fa-dashboard w-5"></i> <span>Dashboard</span>
                </a>
                <a href="produtos.php" class="nav-item active">
                    <i class="fas fa-box w-5"></i> <span>Produtos</span>
                </a>
                <a href="categorias.php" class="nav-item">
                    <i class="fas fa-tags w-5"></i> <span>Categorias</span>
                </a>
                <a href="pedidos.php" class="nav-item">
                    <i class="fas fa-shopping-cart w-5"></i> <span>Pedidos</span>
                </a>
                <a href="clientes.php" class="nav-item">
                    <i class="fas fa-users w-5"></i> <span>Clientes</span>
                </a>
                <a href="integracao.php" class="nav-item">
                    <i class="fas fa-plug w-5"></i> <span>Integrações</span>
                </a>
                <a href="configuracoes.php" class="nav-item">
                    <i class="fas fa-cog w-5"></i> <span>Configurações</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 w-full md:w-auto p-4 md:p-8 overflow-x-hidden">
            <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h1 class="text-2xl md:text-3xl font-bold gradient-text">Gerenciar Produtos</h1>
                <button onclick="novoProduto()" class="btn-primary text-xs md:text-sm w-full sm:w-auto">
                    <i class="fas fa-plus"></i> <span>Novo Produto</span>
                </button>
            </div>

            <!-- Filtros -->
            <div class="bg-slate-800/50 rounded-lg md:rounded-xl p-4 md:p-6 mb-6 backdrop-blur-sm border border-purple-800/30">
                <form method="GET" class="flex flex-col md:flex-row gap-2 md:gap-4">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Buscar..." 
                           class="flex-1 px-3 md:px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-xs md:text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                    
                    <select name="categoria" class="px-3 md:px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-xs md:text-sm text-slate-100 focus:outline-none focus:border-purple-600">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn-primary text-xs md:text-sm w-full md:w-auto">
                        <i class="fas fa-search"></i> <span>Filtrar</span>
                    </button>
                </form>
            </div>

            <!-- Tabela de Produtos -->
            <div class="bg-slate-800/50 rounded-lg md:rounded-xl overflow-hidden backdrop-blur-sm border border-purple-800/30">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs md:text-sm">
                        <thead class="bg-slate-900/50 border-b border-purple-800/30">
                            <tr>
                                <th class="px-3 md:px-6 py-2 md:py-4 text-left font-medium text-purple-400 uppercase">ID</th>
                                <th class="px-3 md:px-6 py-2 md:py-4 text-left font-medium text-purple-400 uppercase hidden sm:table-cell">Imagem</th>
                                <th class="px-3 md:px-6 py-2 md:py-4 text-left font-medium text-purple-400 uppercase">Nome</th>
                                <th class="px-3 md:px-6 py-2 md:py-4 text-left font-medium text-purple-400 uppercase hidden md:table-cell">Categoria</th>
                                <th class="px-3 md:px-6 py-2 md:py-4 text-left font-medium text-purple-400 uppercase">Preço</th>
                                <th class="px-3 md:px-6 py-2 md:py-4 text-left font-medium text-purple-400 uppercase hidden lg:table-cell">Estoque</th>
                                <th class="px-3 md:px-6 py-2 md:py-4 text-left font-medium text-purple-400 uppercase hidden xl:table-cell">Status</th>
                                <th class="px-3 md:px-6 py-2 md:py-4 text-left font-medium text-purple-400 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-purple-800/20">
                            <?php if (empty($produtos)): ?>
                                <tr>
                                    <td colspan="8" class="px-3 md:px-6 py-6 md:py-12 text-center text-slate-400 text-xs md:text-sm">
                                        <i class="fas fa-box-open text-2xl md:text-4xl mb-4"></i>
                                        <p>Nenhum produto encontrado</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($produtos as $produto): ?>
                                    <tr class="hover:bg-slate-900/30 transition">
                                        <td class="px-3 md:px-6 py-2 md:py-4 font-medium">#<?php echo $produto['id']; ?></td>
                                        <td class="px-3 md:px-6 py-2 md:py-4 hidden sm:table-cell">
                                            <div class="w-10 h-10 bg-slate-700 rounded-lg overflow-hidden">
                                                <?php if ($produto['imagem']): ?>
                                                    <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" alt="" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <div class="w-full h-full flex items-center justify-center text-slate-500 text-xs">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-3 md:px-6 py-2 md:py-4">
                                            <div>
                                                <p class="font-medium truncate"><?php echo htmlspecialchars($produto['nome']); ?></p>
                                                <p class="text-xs text-slate-400">SKU: <?php echo htmlspecialchars($produto['sku'] ?? 'N/A'); ?></p>
                                            </div>
                                        </td>
                                        <td class="px-3 md:px-6 py-2 md:py-4 hidden md:table-cell">
                                            <span class="px-2 py-1 bg-purple-900/30 text-purple-400 rounded text-xs">
                                                <?php echo htmlspecialchars($produto['categoria_nome'] ?? '-'); ?>
                                            </span>
                                        </td>
                                        <td class="px-3 md:px-6 py-2 md:py-4">
                                            <div>
                                                <p class="font-medium">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                                <?php if ($produto['preco_promocional']): ?>
                                                    <p class="text-xs text-green-400">R$ <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-3 md:px-6 py-2 md:py-4 hidden lg:table-cell">
                                            <span class="px-2 py-1 <?php echo $produto['estoque'] > 10 ? 'bg-green-900/30 text-green-400' : ($produto['estoque'] > 0 ? 'bg-yellow-900/30 text-yellow-400' : 'bg-red-900/30 text-red-400'); ?> rounded text-xs">
                                                <?php echo $produto['estoque']; ?> un
                                            </span>
                                        </td>
                                        <td class="px-3 md:px-6 py-2 md:py-4 hidden xl:table-cell">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" <?php echo $produto['ativo'] ? 'checked' : ''; ?> 
                                                       onchange="toggleStatus(<?php echo $produto['id']; ?>, this.checked)" 
                                                       class="sr-only peer">
                                                <div class="w-9 h-5 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                                            </label>
                                        </td>
                                        <td class="px-3 md:px-6 py-2 md:py-4">
                                            <div class="flex gap-1 md:gap-2">
                                                <button onclick="editarProduto(<?php echo $produto['id']; ?>)" 
                                                        class="p-1 md:p-2 bg-blue-900/30 text-blue-400 rounded hover:bg-blue-900/50 transition" title="Editar">
                                                    <i class="fas fa-edit text-xs md:text-sm"></i>
                                                </button>
                                                <button onclick="deletarProduto(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>')" 
                                                        class="p-1 md:p-2 bg-red-900/30 text-red-400 rounded hover:bg-red-900/50 transition" title="Deletar">
                                                    <i class="fas fa-trash text-xs md:text-sm"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex justify-center gap-2 flex-wrap">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&categoria=<?php echo urlencode($categoria); ?>" 
                           class="px-2 md:px-4 py-1 md:py-2 rounded text-xs md:text-sm <?php echo $i == $page ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'; ?> transition">
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
            margin-bottom: 0.25rem;
            border-radius: 0.5rem;
            color: #94a3b8;
            transition: all 0.3s;
            text-decoration: none;
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
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
            border-radius: 0.5rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
    </style>

    <script>
        // Menu mobile toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        }

        sidebarToggle.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        });

        sidebarOverlay.addEventListener('click', closeSidebar);

        document.querySelectorAll('.nav-item').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    closeSidebar();
                }
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            }
        });

        function novoProduto() {
            window.location.href = 'produto-form.php';
        }
        
        function editarProduto(id) {
            window.location.href = 'produto-form.php?id=' + id;
        }
        
        function toggleStatus(id, status) {
            fetch('../api/produtos.php', {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id, ativo: status})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status atualizado!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
        
        function deletarProduto(id, nome) {
            Swal.fire({
                title: 'Tem certeza?',
                text: `Deseja excluir "${nome}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/produtos.php', {
                        method: 'DELETE',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id: id})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Excluído!', 'Produto removido com sucesso.', 'success')
                            .then(() => location.reload());
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>

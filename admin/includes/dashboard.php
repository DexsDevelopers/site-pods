<div class="space-y-6">
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    
    try {
        include '../../includes/config.php';
        include '../../includes/db.php';
        include '../../includes/helpers.php';
        
        $conn = Database::getConnection();
        
        // Métricas
        $today = date('Y-m-d');
        
        // Vendas hoje
        $stmt = $conn->prepare(
            "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total 
             FROM orders WHERE DATE(criado_em) = ?"
        );
        $stmt->execute([$today]);
        $vendas_hoje = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Receita mês
        $primeiro_dia = date('Y-m-01');
        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(total), 0) as total FROM orders 
             WHERE DATE(criado_em) >= ?"
        );
        $stmt->execute([$primeiro_dia]);
        $receita_mes = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Pedidos pendentes
        $stmt = $conn->prepare(
            "SELECT COUNT(*) as count FROM orders WHERE status = 'pendente'"
        );
        $stmt->execute();
        $pedidos_pendentes = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Total de produtos
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
        $stmt->execute();
        $total_produtos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Total de clientes
        $stmt = $conn->prepare(
            "SELECT COUNT(*) as count FROM users WHERE role = 'customer'"
        );
        $stmt->execute();
        $total_clientes = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logError("Erro ao carregar dashboard: " . $e->getMessage());
        $vendas_hoje = ['count' => 0, 'total' => 0];
        $receita_mes = ['total' => 0];
        $pedidos_pendentes = ['count' => 0];
        $total_produtos = ['count' => 0];
        $total_clientes = ['count' => 0];
    }
    ?>

    <!-- Métricas -->
    <div class="grid md:grid-cols-4 gap-6 mb-6">
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Vendas Hoje</p>
                    <p class="text-3xl font-black"><?php echo $vendas_hoje['count'] ?? 0; ?></p>
                </div>
                <i class="fas fa-shopping-bag text-purple-500 text-3xl opacity-20"></i>
            </div>
        </div>
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Receita Mês</p>
                    <p class="text-3xl font-black">R$ <?php echo number_format($receita_mes['total'] ?? 0, 0, ',', '.'); ?></p>
                </div>
                <i class="fas fa-chart-line text-green-500 text-3xl opacity-20"></i>
            </div>
        </div>
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Pedidos Pendentes</p>
                    <p class="text-3xl font-black text-orange-400"><?php echo $pedidos_pendentes['count'] ?? 0; ?></p>
                </div>
                <i class="fas fa-clock text-orange-500 text-3xl opacity-20"></i>
            </div>
        </div>
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Clientes</p>
                    <p class="text-3xl font-black"><?php echo $total_clientes['count'] ?? 0; ?></p>
                </div>
                <i class="fas fa-users text-blue-500 text-3xl opacity-20"></i>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="glass border border-purple-600/30 rounded-lg p-6 mb-6">
        <h3 class="text-xl font-black mb-4">Ações Rápidas</h3>
        <div class="grid md:grid-cols-4 gap-4">
            <a href="?page=products&action=add" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-center hover:shadow-lg transition">
                <i class="fas fa-plus mr-2"></i>Novo Produto
            </a>
            <a href="?page=categories&action=add" class="px-6 py-3 bg-slate-700 rounded-lg font-bold text-center hover:bg-slate-600 transition">
                <i class="fas fa-plus mr-2"></i>Nova Categoria
            </a>
            <a href="?page=orders" class="px-6 py-3 bg-slate-700 rounded-lg font-bold text-center hover:bg-slate-600 transition">
                <i class="fas fa-eye mr-2"></i>Ver Pedidos
            </a>
            <a href="?page=customers" class="px-6 py-3 bg-slate-700 rounded-lg font-bold text-center hover:bg-slate-600 transition">
                <i class="fas fa-users mr-2"></i>Ver Clientes
            </a>
        </div>
    </div>

    <!-- Pedidos Recentes -->
    <div class="glass border border-purple-600/30 rounded-lg overflow-hidden">
        <div class="bg-slate-800/50 border-b border-slate-700 px-6 py-4">
            <h3 class="text-xl font-black">Pedidos Recentes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-800/30 border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left">ID</th>
                        <th class="px-6 py-3 text-left">Cliente</th>
                        <th class="px-6 py-3 text-left">Total</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    <?php
                    try {
                        $stmt = $conn->prepare(
                            "SELECT o.id, u.nome, o.total, o.status, o.criado_em 
                             FROM orders o 
                             JOIN users u ON o.user_id = u.id 
                             ORDER BY o.criado_em DESC LIMIT 5"
                        );
                        $stmt->execute();
                        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($pedidos as $pedido):
                    ?>
                    <tr class="hover:bg-white/5 transition">
                        <td class="px-6 py-3">#<?php echo $pedido['id']; ?></td>
                        <td class="px-6 py-3"><?php echo htmlspecialchars($pedido['nome']); ?></td>
                        <td class="px-6 py-3">R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                <?php 
                                echo match($pedido['status']) {
                                    'pendente' => 'bg-yellow-600/20 text-yellow-400',
                                    'enviado' => 'bg-blue-600/20 text-blue-400',
                                    'entregue' => 'bg-green-600/20 text-green-400',
                                    'cancelado' => 'bg-red-600/20 text-red-400',
                                    default => 'bg-slate-600/20 text-slate-400'
                                };
                                ?>">
                                <?php echo ucfirst($pedido['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-3 text-slate-400"><?php echo date('d/m/Y H:i', strtotime($pedido['criado_em'])); ?></td>
                    </tr>
                    <?php
                        endforeach;
                    } catch (Exception $e) {
                        echo '<tr><td colspan="5" class="px-6 py-3 text-center text-slate-400">Erro ao carregar pedidos</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

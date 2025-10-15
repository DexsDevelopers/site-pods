<div class="space-y-6">
    <?php
    include '../../includes/db.php';
    $db = Database::getInstance();
    
    // Métricas
    $today = date('Y-m-d');
    
    // Vendas hoje
    $stmt = $db->getConnection()->prepare(
        "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total 
         FROM orders WHERE DATE(criado_em) = ?"
    );
    $stmt->execute([$today]);
    $vendas_hoje = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Receita mês
    $primeiro_dia = date('Y-m-01');
    $stmt = $db->getConnection()->prepare(
        "SELECT COALESCE(SUM(total), 0) as total FROM orders 
         WHERE DATE(criado_em) >= ?"
    );
    $stmt->execute([$primeiro_dia]);
    $receita_mes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Pedidos pendentes
    $stmt = $db->getConnection()->prepare(
        "SELECT COUNT(*) as count FROM orders WHERE status = 'pendente'"
    );
    $stmt->execute();
    $pedidos_pendentes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Total de produtos
    $stmt = $db->getConnection()->prepare("SELECT COUNT(*) as count FROM products");
    $stmt->execute();
    $total_produtos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Total de clientes
    $stmt = $db->getConnection()->prepare(
        "SELECT COUNT(*) as count FROM users WHERE role = 'customer'"
    );
    $stmt->execute();
    $total_clientes = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>

    <!-- Métricas -->
    <div class="grid md:grid-cols-4 gap-6 mb-6">
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Vendas Hoje</p>
                    <p class="text-3xl font-black"><?php echo $vendas_hoje['count']; ?></p>
                </div>
                <i class="fas fa-shopping-bag text-purple-500 text-3xl opacity-20"></i>
            </div>
        </div>
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Receita Mês</p>
                    <p class="text-3xl font-black">R$ <?php echo number_format($receita_mes['total'], 0, ',', '.'); ?></p>
                </div>
                <i class="fas fa-chart-line text-green-500 text-3xl opacity-20"></i>
            </div>
        </div>
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Pedidos Pendentes</p>
                    <p class="text-3xl font-black text-orange-400"><?php echo $pedidos_pendentes['count']; ?></p>
                </div>
                <i class="fas fa-clock text-orange-500 text-3xl opacity-20"></i>
            </div>
        </div>
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Clientes</p>
                    <p class="text-3xl font-black"><?php echo $total_clientes['count']; ?></p>
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

    <!-- Últimos Pedidos -->
    <div class="glass border border-purple-600/30 rounded-lg p-6">
        <h3 class="text-xl font-black mb-4">Últimos Pedidos</h3>
        <?php
        $stmt = $db->getConnection()->prepare(
            "SELECT o.id, o.total, o.status, o.criado_em, u.nome 
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             ORDER BY o.criado_em DESC 
             LIMIT 5"
        );
        $stmt->execute();
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <table class="w-full text-sm">
            <thead class="border-b border-slate-700">
                <tr>
                    <th class="text-left py-2">Pedido</th>
                    <th class="text-left py-2">Cliente</th>
                    <th class="text-left py-2">Valor</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-left py-2">Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                <tr class="border-b border-slate-700 hover:bg-white/5 transition">
                    <td class="py-3">#<?php echo str_pad($pedido['id'], 3, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($pedido['nome']); ?></td>
                    <td>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                    <td>
                        <span class="px-2 py-1 rounded text-xs font-bold <?php 
                            echo match($pedido['status']) {
                                'entregue' => 'bg-green-600/20 text-green-400',
                                'enviado' => 'bg-blue-600/20 text-blue-400',
                                'cancelado' => 'bg-red-600/20 text-red-400',
                                default => 'bg-orange-600/20 text-orange-400'
                            };
                        ?>">
                            <?php echo ucfirst($pedido['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['criado_em'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

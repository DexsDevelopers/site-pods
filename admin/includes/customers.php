<div class="space-y-6">
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    
    try {
        include '../../includes/config.php';
        include '../../includes/db.php';
        include '../../includes/helpers.php';
        
        $conn = Database::getConnection();
        
        $action = $_GET['action'] ?? null;
        $customer_id = $_GET['id'] ?? null;
        
        if ($action === 'detail' && $customer_id) {
            $stmt = $conn->prepare(
                "SELECT u.*, COUNT(o.id) as total_pedidos, COALESCE(SUM(o.total), 0) as total_gasto 
                 FROM users u 
                 LEFT JOIN orders o ON u.id = o.user_id 
                 WHERE u.id = ? AND u.role = 'customer' 
                 GROUP BY u.id"
            );
            $stmt->execute([$customer_id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($customer) {
                $stmt = $conn->prepare(
                    "SELECT id, total, status, criado_em FROM orders 
                     WHERE user_id = ? ORDER BY criado_em DESC LIMIT 20"
                );
                $stmt->execute([$customer_id]);
                $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="glass border border-purple-600/30 rounded-lg p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-black"><?php echo htmlspecialchars($customer['nome']); ?></h3>
                        <a href="?page=customers" class="px-4 py-2 bg-slate-700 rounded">← Voltar</a>
                    </div>
                    
                    <div class="grid md:grid-cols-4 gap-4 mb-6">
                        <div class="border border-purple-600/30 rounded p-4">
                            <p class="text-slate-400">Email</p>
                            <p class="font-bold"><?php echo htmlspecialchars($customer['email']); ?></p>
                        </div>
                        <div class="border border-purple-600/30 rounded p-4">
                            <p class="text-slate-400">Telefone</p>
                            <p class="font-bold"><?php echo htmlspecialchars($customer['telefone'] ?? '-'); ?></p>
                        </div>
                        <div class="border border-purple-600/30 rounded p-4">
                            <p class="text-slate-400">Total de Pedidos</p>
                            <p class="text-2xl font-black"><?php echo $customer['total_pedidos']; ?></p>
                        </div>
                        <div class="border border-purple-600/30 rounded p-4">
                            <p class="text-slate-400">Total Gasto</p>
                            <p class="text-2xl font-black">R$ <?php echo number_format($customer['total_gasto'], 2, ',', '.'); ?></p>
                        </div>
                    </div>
                    
                    <h4 class="font-bold text-purple-400 mb-3">Pedidos Recentes</h4>
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-700">
                            <tr>
                                <th class="text-left py-2">Pedido</th>
                                <th class="text-left py-2">Total</th>
                                <th class="text-left py-2">Status</th>
                                <th class="text-left py-2">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $ped): ?>
                            <tr class="border-b border-slate-700">
                                <td>#<?php echo str_pad($ped['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td>R$ <?php echo number_format($ped['total'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="px-2 py-1 rounded text-xs font-bold <?php 
                                        echo match($ped['status']) {
                                            'entregue' => 'bg-green-600/20 text-green-400',
                                            'enviado' => 'bg-blue-600/20 text-blue-400',
                                            'cancelado' => 'bg-red-600/20 text-red-400',
                                            default => 'bg-orange-600/20 text-orange-400'
                                        };
                                    ?>">
                                        <?php echo ucfirst($ped['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($ped['criado_em'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
        } else {
            $stmt = $conn->prepare(
                "SELECT u.*, COUNT(o.id) as total_pedidos, COALESCE(SUM(o.total), 0) as total_gasto 
                 FROM users u 
                 LEFT JOIN orders o ON u.id = o.user_id 
                 WHERE u.role = 'customer' 
                 GROUP BY u.id 
                 ORDER BY u.criado_em DESC 
                 LIMIT 100"
            );
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black">👥 Clientes</h3>
            </div>
            
            <div class="glass border border-purple-600/30 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-800/50 border-b border-slate-700">
                        <tr>
                            <th class="text-left px-6 py-3">Nome</th>
                            <th class="text-left px-6 py-3">Email</th>
                            <th class="text-left px-6 py-3">Telefone</th>
                            <th class="text-left px-6 py-3">Pedidos</th>
                            <th class="text-left px-6 py-3">Total Gasto</th>
                            <th class="text-left px-6 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <?php foreach ($customers as $cust): ?>
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-3 font-bold"><?php echo htmlspecialchars($cust['nome']); ?></td>
                            <td class="px-6 py-3"><?php echo htmlspecialchars($cust['email']); ?></td>
                            <td class="px-6 py-3"><?php echo htmlspecialchars($cust['telefone'] ?? '-'); ?></td>
                            <td class="px-6 py-3"><?php echo $cust['total_pedidos']; ?></td>
                            <td class="px-6 py-3">R$ <?php echo number_format($cust['total_gasto'], 2, ',', '.'); ?></td>
                            <td class="px-6 py-3">
                                <a href="?page=customers&action=detail&id=<?php echo $cust['id']; ?>" class="px-3 py-1 bg-blue-600/20 text-blue-400 rounded text-xs hover:bg-blue-600/40 transition">👁️ Ver</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    } catch (Exception $e) {
        echo '<div class="bg-red-600/20 border border-red-600 text-red-400 px-4 py-3 rounded mb-6">Erro: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    ?>
</div>

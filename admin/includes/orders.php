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
        $order_id = $_GET['id'] ?? null;
        
        if ($action === 'update_status' && $order_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? null;
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $order_id])) {
                echo '<div class="bg-green-600/20 border border-green-600 text-green-400 px-4 py-3 rounded mb-6">✅ Status atualizado!</div>';
                header('Refresh: 1; url=?page=orders');
            }
        }
        
        if ($action === 'detail' && $order_id) {
            $stmt = $conn->prepare(
                "SELECT o.*, u.nome as cliente_nome, u.email, u.telefone 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 WHERE o.id = ?"
            );
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order) {
                $stmt = $conn->prepare(
                    "SELECT oi.*, p.nome as produto_nome FROM order_items oi 
                     JOIN products p ON oi.product_id = p.id 
                     WHERE oi.order_id = ?"
                );
                $stmt->execute([$order_id]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="glass border border-purple-600/30 rounded-lg p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-black">Pedido #<?php echo str_pad($order['id'], 3, '0', STR_PAD_LEFT); ?></h3>
                        <a href="?page=orders" class="px-4 py-2 bg-slate-700 rounded">← Voltar</a>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="font-bold text-purple-400 mb-3">Informações do Cliente</h4>
                            <p><strong>Nome:</strong> <?php echo htmlspecialchars($order['cliente_nome']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($order['telefone']); ?></p>
                        </div>
                        <div>
                            <h4 class="font-bold text-purple-400 mb-3">Status do Pedido</h4>
                            <form method="POST" class="space-y-2">
                                <select name="status" class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded">
                                    <option value="pendente" <?php echo $order['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="enviado" <?php echo $order['status'] === 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                                    <option value="entregue" <?php echo $order['status'] === 'entregue' ? 'selected' : ''; ?>>Entregue</option>
                                    <option value="cancelado" <?php echo $order['status'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 rounded font-bold">✅ Atualizar</button>
                            </form>
                        </div>
                    </div>
                    
                    <h4 class="font-bold text-purple-400 mb-3">Itens do Pedido</h4>
                    <div class="bg-slate-800/30 rounded p-4 mb-6">
                        <table class="w-full text-sm">
                            <thead class="border-b border-slate-700">
                                <tr>
                                    <th class="text-left py-2">Produto</th>
                                    <th class="text-left py-2">Quantidade</th>
                                    <th class="text-left py-2">Preço</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr class="border-b border-slate-700">
                                    <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                                    <td><?php echo $item['quantidade']; ?></td>
                                    <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-right">
                        <p class="text-lg font-black">Total: R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></p>
                    </div>
                </div>
                <?php
            }
        } else {
            $stmt = $conn->prepare(
                "SELECT o.*, u.nome as cliente_nome 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 ORDER BY o.criado_em DESC 
                 LIMIT 100"
            );
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black">🛒 Pedidos</h3>
            </div>
            
            <div class="glass border border-purple-600/30 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-800/50 border-b border-slate-700">
                        <tr>
                            <th class="text-left px-6 py-3">Pedido</th>
                            <th class="text-left px-6 py-3">Cliente</th>
                            <th class="text-left px-6 py-3">Total</th>
                            <th class="text-left px-6 py-3">Status</th>
                            <th class="text-left px-6 py-3">Data</th>
                            <th class="text-left px-6 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-3">#<?php echo str_pad($order['id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td class="px-6 py-3"><?php echo htmlspecialchars($order['cliente_nome']); ?></td>
                            <td class="px-6 py-3">R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                            <td class="px-6 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    <?php echo match($order['status']) {
                                        'pendente' => 'bg-yellow-600/20 text-yellow-400',
                                        'enviado' => 'bg-blue-600/20 text-blue-400',
                                        'entregue' => 'bg-green-600/20 text-green-400',
                                        'cancelado' => 'bg-red-600/20 text-red-400',
                                        default => 'bg-slate-600/20 text-slate-400'
                                    }; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-400"><?php echo date('d/m/Y H:i', strtotime($order['criado_em'])); ?></td>
                            <td class="px-6 py-3">
                                <a href="?page=orders&action=detail&id=<?php echo $order['id']; ?>" class="px-3 py-1 bg-blue-600/20 text-blue-400 rounded text-xs hover:bg-blue-600/40 transition">👁️ Ver</a>
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

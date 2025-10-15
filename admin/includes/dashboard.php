<?php
$conn = null;
$metrics = [
    'vendas_hoje' => 0,
    'receita_mes' => 0,
    'pedidos_pendentes' => 0,
    'total_produtos' => 0,
    'total_clientes' => 0
];
$recent_orders = [];

try {
    @include '../../includes/config.php';
    @include '../../includes/db.php';
    $conn = Database::getConnection();
} catch (Exception $e) {
    echo '<div style="color: #f87171; background: rgba(220,38,38,0.2); padding: 16px; border-radius: 8px;">❌ Erro</div>';
}

if ($conn) {
    try {
        // Vendas hoje
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE(criado_em) = CURDATE()");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $metrics['vendas_hoje'] = $r['count'] ?? 0;
        
        // Receita do mês
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE MONTH(criado_em) = MONTH(NOW()) AND YEAR(criado_em) = YEAR(NOW())");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $metrics['receita_mes'] = $r['total'] ?? 0;
        
        // Pedidos pendentes
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE status IN ('pendente', 'enviado')");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $metrics['pedidos_pendentes'] = $r['count'] ?? 0;
        
        // Total de produtos
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $metrics['total_produtos'] = $r['count'] ?? 0;
        
        // Total de clientes
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $metrics['total_clientes'] = $r['count'] ?? 0;
        
        // Pedidos recentes
        $stmt = $conn->prepare("SELECT o.id, o.total, o.status, o.criado_em, u.nome FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.criado_em DESC LIMIT 10");
        $stmt->execute();
        $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Metric Card -->
    <div style="background: linear-gradient(135deg, rgba(147,51,234,0.2), rgba(236,72,153,0.2)); border: 1px solid rgba(147,51,234,0.3); border-radius: 8px; padding: 20px;">
        <p style="color: #9333ea; font-size: 12px; margin: 0; text-transform: uppercase;">Vendas Hoje</p>
        <p style="font-size: 32px; font-weight: 900; margin: 8px 0 0 0;"><?php echo $metrics['vendas_hoje']; ?></p>
    </div>

    <div style="background: linear-gradient(135deg, rgba(34,197,94,0.2), rgba(74,222,128,0.2)); border: 1px solid rgba(34,197,94,0.3); border-radius: 8px; padding: 20px;">
        <p style="color: #22c55e; font-size: 12px; margin: 0; text-transform: uppercase;">Receita Mês</p>
        <p style="font-size: 32px; font-weight: 900; margin: 8px 0 0 0;">R$ <?php echo number_format($metrics['receita_mes'], 2, ',', '.'); ?></p>
    </div>

    <div style="background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(96,165,250,0.2)); border: 1px solid rgba(59,130,246,0.3); border-radius: 8px; padding: 20px;">
        <p style="color: #3b82f6; font-size: 12px; margin: 0; text-transform: uppercase;">Pedidos Pendentes</p>
        <p style="font-size: 32px; font-weight: 900; margin: 8px 0 0 0;"><?php echo $metrics['pedidos_pendentes']; ?></p>
    </div>

    <div style="background: linear-gradient(135deg, rgba(249,115,22,0.2), rgba(251,146,60,0.2)); border: 1px solid rgba(249,115,22,0.3); border-radius: 8px; padding: 20px;">
        <p style="color: #f97316; font-size: 12px; margin: 0; text-transform: uppercase;">Produtos</p>
        <p style="font-size: 32px; font-weight: 900; margin: 8px 0 0 0;"><?php echo $metrics['total_produtos']; ?></p>
    </div>

    <div style="background: linear-gradient(135deg, rgba(236,72,153,0.2), rgba(244,114,182,0.2)); border: 1px solid rgba(236,72,153,0.3); border-radius: 8px; padding: 20px;">
        <p style="color: #ec4899; font-size: 12px; margin: 0; text-transform: uppercase;">Clientes</p>
        <p style="font-size: 32px; font-weight: 900; margin: 8px 0 0 0;"><?php echo $metrics['total_clientes']; ?></p>
    </div>
</div>

<div style="background: rgba(0,0,0,0.1); border: 1px solid rgba(147,51,234,0.3); border-radius: 8px; padding: 20px;">
    <h3 style="font-size: 18px; font-weight: 900; margin: 0 0 16px 0;">📊 Últimos Pedidos</h3>
    
    <?php if (count($recent_orders) > 0): ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead style="background: rgba(0,0,0,0.2);">
                <tr>
                    <th style="padding: 12px; text-align: left;">Pedido</th>
                    <th style="padding: 12px; text-align: left;">Cliente</th>
                    <th style="padding: 12px; text-align: left;">Total</th>
                    <th style="padding: 12px; text-align: left;">Status</th>
                    <th style="padding: 12px; text-align: left;">Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): ?>
                <tr style="border-bottom: 1px solid rgba(147,51,234,0.15);">
                    <td style="padding: 12px;">#<?php echo $order['id']; ?></td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($order['nome']); ?></td>
                    <td style="padding: 12px;">R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                    <td style="padding: 12px;"><span style="padding: 4px 8px; background: <?php echo $order['status'] === 'entregue' ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.2)'; ?>; color: <?php echo $order['status'] === 'entregue' ? '#86efac' : '#fca5a5'; ?>; border-radius: 4px; font-size: 12px;"><?php echo ucfirst($order['status']); ?></span></td>
                    <td style="padding: 12px; font-size: 12px;"><?php echo date('d/m/Y', strtotime($order['criado_em'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p style="color: #94a3b8; margin: 0;">Nenhum pedido recente</p>
    <?php endif; ?>
</div>

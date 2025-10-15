<?php
$action = $_GET['action'] ?? null;
$customer_id = $_GET['id'] ?? null;
$customers = [];
$customer = null;
$customer_orders = [];
$conn = null;

try {
    @include '../../includes/config.php';
    @include '../../includes/db.php';
    $conn = Database::getConnection();
} catch (Exception $e) {
    echo '<div style="color: #f87171; background: rgba(220,38,38,0.2); padding: 16px; border-radius: 8px;">❌ Erro</div>';
}

if ($conn) {
    if ($action === 'detail' && $customer_id) {
        try {
            $stmt = $conn->prepare("SELECT u.*, COUNT(o.id) as total_pedidos, COALESCE(SUM(o.total), 0) as total_gasto FROM users u LEFT JOIN orders o ON u.id = o.user_id WHERE u.id = ? AND u.role = 'customer' GROUP BY u.id");
            $stmt->execute([$customer_id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($customer) {
                $stmt = $conn->prepare("SELECT id, total, status, criado_em FROM orders WHERE user_id = ? ORDER BY criado_em DESC LIMIT 20");
                $stmt->execute([$customer_id]);
                $customer_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {}
    }

    if (!$action) {
        try {
            $stmt = $conn->prepare("SELECT u.*, COUNT(o.id) as total_pedidos, COALESCE(SUM(o.total), 0) as total_gasto FROM users u LEFT JOIN orders o ON u.id = o.user_id WHERE u.role = 'customer' GROUP BY u.id ORDER BY u.id DESC LIMIT 50");
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
}
?>

<?php if ($action === 'detail' && $customer): ?>

<div style="background: rgba(0,0,0,0.1); border: 1px solid rgba(147,51,234,0.3); border-radius: 8px; padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h3 style="font-size: 20px; font-weight: 900; margin: 0;"><?php echo htmlspecialchars($customer['nome']); ?></h3>
        <a href="?page=customers" style="padding: 8px 16px; background: #475569; color: white; text-decoration: none; border-radius: 6px; font-size: 14px;">← Voltar</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
        <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 6px;">
            <p style="color: #9333ea; font-size: 12px; margin: 0;">Email</p>
            <p style="font-weight: bold; margin: 0; word-break: break-all;"><?php echo htmlspecialchars($customer['email']); ?></p>
        </div>
        <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 6px;">
            <p style="color: #9333ea; font-size: 12px; margin: 0;">Telefone</p>
            <p style="font-weight: bold; margin: 0;"><?php echo htmlspecialchars($customer['telefone'] ?? '-'); ?></p>
        </div>
        <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 6px;">
            <p style="color: #9333ea; font-size: 12px; margin: 0;">Pedidos</p>
            <p style="font-weight: bold; font-size: 24px; margin: 0;"><?php echo $customer['total_pedidos']; ?></p>
        </div>
        <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 6px;">
            <p style="color: #9333ea; font-size: 12px; margin: 0;">Total Gasto</p>
            <p style="font-weight: bold; margin: 0;">R$ <?php echo number_format($customer['total_gasto'], 2, ',', '.'); ?></p>
        </div>
    </div>

    <h4 style="color: #9333ea; font-weight: bold; margin: 0 0 12px 0;">Pedidos Recentes</h4>
    <div style="background: rgba(0,0,0,0.2); border-radius: 6px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead style="background: rgba(0,0,0,0.3);">
                <tr>
                    <th style="padding: 12px; text-align: left;">Pedido</th>
                    <th style="padding: 12px; text-align: left;">Total</th>
                    <th style="padding: 12px; text-align: left;">Status</th>
                    <th style="padding: 12px; text-align: left;">Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customer_orders as $o): ?>
                <tr style="border-top: 1px solid rgba(147,51,234,0.15);">
                    <td style="padding: 12px;">#<?php echo $o['id']; ?></td>
                    <td style="padding: 12px;">R$ <?php echo number_format($o['total'], 2, ',', '.'); ?></td>
                    <td style="padding: 12px;"><span style="padding: 2px 6px; background: <?php echo $o['status'] === 'entregue' ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.2)'; ?>; color: <?php echo $o['status'] === 'entregue' ? '#86efac' : '#fca5a5'; ?>; border-radius: 3px; font-size: 12px;"><?php echo ucfirst($o['status']); ?></span></td>
                    <td style="padding: 12px; font-size: 12px;"><?php echo date('d/m/Y', strtotime($o['criado_em'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h3 style="font-size: 20px; font-weight: 900; margin: 0;">👥 Clientes</h3>
</div>

<?php if (count($customers) > 0): ?>

<div style="background: rgba(0,0,0,0.1); border: 1px solid rgba(147,51,234,0.3); border-radius: 8px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <thead style="background: rgba(0,0,0,0.2);">
            <tr>
                <th style="padding: 12px; text-align: left;">Nome</th>
                <th style="padding: 12px; text-align: left;">Email</th>
                <th style="padding: 12px; text-align: left;">Telefone</th>
                <th style="padding: 12px; text-align: left;">Pedidos</th>
                <th style="padding: 12px; text-align: left;">Total Gasto</th>
                <th style="padding: 12px; text-align: left;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $c): ?>
            <tr style="border-bottom: 1px solid rgba(147,51,234,0.15);">
                <td style="padding: 12px;"><?php echo htmlspecialchars($c['nome']); ?></td>
                <td style="padding: 12px; font-size: 12px;"><?php echo htmlspecialchars($c['email']); ?></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($c['telefone'] ?? '-'); ?></td>
                <td style="padding: 12px;"><?php echo $c['total_pedidos']; ?></td>
                <td style="padding: 12px;">R$ <?php echo number_format($c['total_gasto'], 2, ',', '.'); ?></td>
                <td style="padding: 12px;"><a href="?page=customers&action=detail&id=<?php echo $c['id']; ?>" style="padding: 6px 12px; background: rgba(59,130,246,0.2); color: #93c5fd; text-decoration: none; border-radius: 4px; font-size: 12px;">👁️</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php else: ?>

<div style="color: #94a3b8; background: rgba(0,0,0,0.1); padding: 40px; border-radius: 8px; text-align: center;">
    <p>Nenhum cliente</p>
</div>

<?php endif; ?>

<?php endif; ?>

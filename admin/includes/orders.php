<?php
$action = $_GET['action'] ?? null;
$order_id = $_GET['id'] ?? null;
$orders = [];
$order = null;
$order_items = [];
$conn = null;

try {
    @include '../../includes/config.php';
    @include '../../includes/db.php';
    $conn = Database::getConnection();
} catch (Exception $e) {
    echo '<div style="color: #f87171; background: rgba(220,38,38,0.2); padding: 16px; border-radius: 8px;">❌ Erro</div>';
}

if ($conn) {
    if ($action === 'update_status' && $order_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$_POST['status'] ?? 'pendente', $order_id]);
            echo '<div style="color: #86efac; background: rgba(34,197,94,0.2); padding: 16px; border-radius: 8px; margin-bottom: 16px;">✅ Atualizado!</div>';
        } catch (Exception $e) {}
    }

    if ($action === 'detail' && $order_id) {
        try {
            $stmt = $conn->prepare("SELECT o.*, u.nome, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order) {
                $stmt = $conn->prepare("SELECT oi.*, p.nome FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                $stmt->execute([$order_id]);
                $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {}
    }

    if (!$action) {
        try {
            $stmt = $conn->prepare("SELECT o.*, u.nome FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 50");
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
}
?>

<?php if ($action === 'detail' && $order): ?>

<div style="background: rgba(0,0,0,0.1); border: 1px solid rgba(147,51,234,0.3); border-radius: 8px; padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h3 style="font-size: 20px; font-weight: 900; margin: 0;">Pedido #<?php echo $order['id']; ?></h3>
        <a href="?page=orders" style="padding: 8px 16px; background: #475569; color: white; text-decoration: none; border-radius: 6px; font-size: 14px;">← Voltar</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
        <div>
            <h4 style="color: #9333ea; font-weight: bold; margin: 0 0 12px 0;">Cliente</h4>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($order['nome']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
        </div>
        <div>
            <h4 style="color: #9333ea; font-weight: bold; margin: 0 0 12px 0;">Status</h4>
            <form method="POST" style="display: flex; gap: 8px;">
                <select name="status" style="flex: 1; padding: 8px; background: #1e293b; border: 1px solid #9333ea; border-radius: 6px; color: white;">
                    <option value="pendente" <?php echo $order['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="enviado" <?php echo $order['status'] === 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                    <option value="entregue" <?php echo $order['status'] === 'entregue' ? 'selected' : ''; ?>>Entregue</option>
                    <option value="cancelado" <?php echo $order['status'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
                <button type="submit" style="padding: 8px 16px; background: linear-gradient(to right, #9333ea, #ec4899); color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Salvar</button>
            </form>
        </div>
    </div>

    <h4 style="color: #9333ea; font-weight: bold; margin: 0 0 12px 0;">Itens</h4>
    <div style="background: rgba(0,0,0,0.2); border-radius: 6px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead style="background: rgba(0,0,0,0.3);">
                <tr>
                    <th style="padding: 12px; text-align: left;">Produto</th>
                    <th style="padding: 12px; text-align: left;">Qty</th>
                    <th style="padding: 12px; text-align: left;">Preço</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                <tr style="border-top: 1px solid rgba(147,51,234,0.15);">
                    <td style="padding: 12px;"><?php echo htmlspecialchars($item['nome']); ?></td>
                    <td style="padding: 12px;"><?php echo $item['quantidade']; ?></td>
                    <td style="padding: 12px;">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h3 style="font-size: 20px; font-weight: 900; margin: 0;">🛒 Pedidos</h3>
</div>

<?php if (count($orders) > 0): ?>

<div style="background: rgba(0,0,0,0.1); border: 1px solid rgba(147,51,234,0.3); border-radius: 8px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <thead style="background: rgba(0,0,0,0.2);">
            <tr>
                <th style="padding: 12px; text-align: left;">ID</th>
                <th style="padding: 12px; text-align: left;">Cliente</th>
                <th style="padding: 12px; text-align: left;">Total</th>
                <th style="padding: 12px; text-align: left;">Status</th>
                <th style="padding: 12px; text-align: left;">Data</th>
                <th style="padding: 12px; text-align: left;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr style="border-bottom: 1px solid rgba(147,51,234,0.15);">
                <td style="padding: 12px;">#<?php echo $o['id']; ?></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($o['nome']); ?></td>
                <td style="padding: 12px;">R$ <?php echo number_format($o['total'], 2, ',', '.'); ?></td>
                <td style="padding: 12px;"><span style="padding: 4px 8px; background: <?php echo $o['status'] === 'entregue' ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.2)'; ?>; color: <?php echo $o['status'] === 'entregue' ? '#86efac' : '#fca5a5'; ?>; border-radius: 4px; font-size: 12px;"><?php echo ucfirst($o['status']); ?></span></td>
                <td style="padding: 12px; font-size: 12px;"><?php echo date('d/m/Y', strtotime($o['criado_em'])); ?></td>
                <td style="padding: 12px;"><a href="?page=orders&action=detail&id=<?php echo $o['id']; ?>" style="padding: 6px 12px; background: rgba(59,130,246,0.2); color: #93c5fd; text-decoration: none; border-radius: 4px; font-size: 12px;">👁️</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php else: ?>

<div style="color: #94a3b8; background: rgba(0,0,0,0.1); padding: 40px; border-radius: 8px; text-align: center;">
    <p>Nenhum pedido</p>
</div>

<?php endif; ?>

<?php endif; ?>

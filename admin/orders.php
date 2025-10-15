<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db_connect.php';
if (empty($_SESSION['admin'])) { redirect('/admin/login'); }

$orders = $pdo->query('SELECT id, customer_name, customer_email, total_amount, status, created_at FROM orders ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Admin · Pedidos';
include __DIR__ . '/../templates/header.php';
?>
<section class="py-12">
  <div class="max-w-7xl mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">Pedidos</h1>
    <div class="table-responsive rounded-2xl card-glass dark:card-glass-dark p-2">
      <table class="table align-middle">
        <thead><tr><th>#</th><th>Cliente</th><th>E-mail</th><th>Total</th><th>Status</th><th>Data</th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td>#<?= (int)$o['id'] ?></td>
            <td><?= e($o['customer_name']) ?></td>
            <td><?= e($o['customer_email']) ?></td>
            <td><?= money((float)$o['total_amount']) ?></td>
            <td><?= e($o['status']) ?></td>
            <td><?= e($o['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php';



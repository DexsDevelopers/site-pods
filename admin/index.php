<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db_connect.php';

if (empty($_SESSION['admin'])) { redirect('/admin/login'); }

$pageTitle = 'Admin · Dashboard';
include __DIR__ . '/../templates/header.php';
?>
<section class="py-12">
  <div class="max-w-7xl mx-auto px-4">
    <h1 class="text-3xl font-bold mb-6">Dashboard</h1>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      $cProd = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
      $cCat = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
      $cOrd = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
      $sumOrd = (float)$pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM orders')->fetchColumn();
      ?>
      <div class="p-6 rounded-2xl card-glass dark:card-glass-dark"><div class="opacity-70">Produtos</div><div class="text-3xl font-bold"><?= $cProd ?></div></div>
      <div class="p-6 rounded-2xl card-glass dark:card-glass-dark"><div class="opacity-70">Categorias</div><div class="text-3xl font-bold"><?= $cCat ?></div></div>
      <div class="p-6 rounded-2xl card-glass dark:card-glass-dark"><div class="opacity-70">Pedidos</div><div class="text-3xl font-bold"><?= $cOrd ?></div></div>
      <div class="p-6 rounded-2xl card-glass dark:card-glass-dark"><div class="opacity-70">Receita</div><div class="text-3xl font-bold"><?= money($sumOrd) ?></div></div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php';



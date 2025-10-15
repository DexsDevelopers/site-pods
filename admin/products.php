<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db_connect.php';
if (empty($_SESSION['admin'])) { redirect('/admin/login'); }

$rows = $pdo->query('SELECT p.id, p.name, p.slug, p.price, p.is_active, c.name AS category FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.created_at DESC')->fetchAll();

$pageTitle = 'Admin · Produtos';
include __DIR__ . '/../templates/header.php';
?>
<section class="py-12">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Produtos</h1>
      <a href="/admin/produto" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Novo</a>
    </div>
    <div class="table-responsive rounded-2xl card-glass dark:card-glass-dark p-2">
      <table class="table align-middle">
        <thead><tr><th>Nome</th><th>Categoria</th><th>Preço</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= e($r['name']) ?></td>
            <td><?= e($r['category'] ?? '—') ?></td>
            <td><?= money((float)$r['price']) ?></td>
            <td><?= $r['is_active']?'<span class="badge text-bg-success">Ativo</span>':'<span class="badge text-bg-secondary">Inativo</span>' ?></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="/admin/produto?id=<?= (int)$r['id'] ?>">Editar</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php';



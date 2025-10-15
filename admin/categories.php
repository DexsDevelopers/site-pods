<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';
if (empty($_SESSION['admin'])) { redirect('/admin/login'); }

if (is_post()) {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $is_active = (int)($_POST['is_active'] ?? 1);
  if ($id) {
    $u = $pdo->prepare('UPDATE categories SET name=:n, is_active=:a WHERE id=:id');
    $u->execute([':n'=>$name, ':a'=>$is_active, ':id'=>$id]);
  } else {
    $i = $pdo->prepare('INSERT INTO categories (name, is_active) VALUES (:n,:a)');
    $i->execute([':n'=>$name, ':a'=>$is_active]);
  }
}

$rows = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Admin · Categorias';
include __DIR__ . '/../templates/header.php';
?>
<section class="py-12">
  <div class="max-w-5xl mx-auto px-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Categorias</h1>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">Nova</button>
    </div>
    <div class="table-responsive rounded-2xl card-glass dark:card-glass-dark p-2">
      <table class="table align-middle">
        <thead><tr><th>Nome</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= e($r['name']) ?></td>
            <td><?= $r['is_active']?'<span class="badge text-bg-success">Ativa</span>':'<span class="badge text-bg-secondary">Inativa</span>' ?></td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" data-category='<?= e(json_encode($r)) ?>'>Editar</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="post">
          <div class="modal-header"><h5 class="modal-title">Categoria</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body space-y-3 p-3">
            <input type="hidden" name="id" id="cat_id">
            <div>
              <label class="form-label">Nome</label>
              <input class="form-control" name="name" id="cat_name" required>
            </div>
            <div>
              <label class="form-label">Ativa</label>
              <select class="form-select" name="is_active" id="cat_active">
                <option value="1">Sim</option>
                <option value="0">Não</option>
              </select>
            </div>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button><button class="btn btn-primary">Salvar</button></div>
        </form>
      </div>
    </div>
  </div>
</section>
<script>
  const modal = document.getElementById('categoryModal');
  modal?.addEventListener('show.bs.modal', (ev) => {
    const btn = ev.relatedTarget;
    const data = btn?.getAttribute('data-category');
    if (data) {
      const obj = JSON.parse(data);
      document.getElementById('cat_id').value = obj.id;
      document.getElementById('cat_name').value = obj.name;
      document.getElementById('cat_active').value = obj.is_active ? '1' : '0';
    } else {
      document.getElementById('cat_id').value = '';
      document.getElementById('cat_name').value = '';
      document.getElementById('cat_active').value = '1';
    }
  });
</script>
<?php include __DIR__ . '/../templates/footer.php';



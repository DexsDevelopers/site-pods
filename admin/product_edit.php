<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';
if (empty($_SESSION['admin'])) { redirect('/admin/login'); }

$id = (int)($_GET['id'] ?? 0);
$cats = $pdo->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();
$prod = null;
if ($id) {
  $s = $pdo->prepare('SELECT * FROM products WHERE id = :id'); $s->execute([':id'=>$id]); $prod = $s->fetch();
}

if (is_post()) {
  $name = trim($_POST['name'] ?? '');
  $slug = trim($_POST['slug'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $category_id = (int)($_POST['category_id'] ?? 0);
  $is_active = (int)($_POST['is_active'] ?? 0);
  $featured = (int)($_POST['featured'] ?? 0);
  $cover_image = trim($_POST['cover_image'] ?? '');
  $description = trim($_POST['description'] ?? '');

  if ($id) {
    $u = $pdo->prepare('UPDATE products SET name=:n, slug=:s, price=:p, category_id=:c, is_active=:a, featured=:f, cover_image=:img, description=:d WHERE id=:id');
    $u->execute([':n'=>$name,':s'=>$slug,':p'=>$price,':c'=>$category_id,':a'=>$is_active,':f'=>$featured,':img'=>$cover_image,':d'=>$description,':id'=>$id]);
  } else {
    $i = $pdo->prepare('INSERT INTO products (name, slug, price, category_id, is_active, featured, cover_image, description) VALUES (:n,:s,:p,:c,:a,:f,:img,:d)');
    $i->execute([':n'=>$name,':s'=>$slug,':p'=>$price,':c'=>$category_id,':a'=>$is_active,':f'=>$featured,':img'=>$cover_image,':d'=>$description]);
    $id = (int)$pdo->lastInsertId();
  }
  redirect('/admin/produtos');
}

$pageTitle = 'Admin · Editar Produto';
include __DIR__ . '/../templates/header.php';
?>
<section class="py-12">
  <div class="max-w-4xl mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6"><?= $id? 'Editar':'Novo' ?> produto</h1>
    <form method="post" class="grid md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="form-label">Nome</label>
        <input class="form-control" name="name" value="<?= e($prod['name'] ?? '') ?>" required>
      </div>
      <div>
        <label class="form-label">Slug</label>
        <input class="form-control" name="slug" value="<?= e($prod['slug'] ?? '') ?>" required>
      </div>
      <div>
        <label class="form-label">Preço</label>
        <input class="form-control" type="number" step="0.01" name="price" value="<?= e($prod['price'] ?? '0') ?>" required>
      </div>
      <div>
        <label class="form-label">Categoria</label>
        <select class="form-select" name="category_id">
          <option value="0">—</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)($prod['category_id'] ?? 0)===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label">Imagem (URL)</label>
        <input class="form-control" name="cover_image" value="<?= e($prod['cover_image'] ?? '') ?>">
      </div>
      <div class="md:col-span-2">
        <label class="form-label">Descrição</label>
        <textarea class="form-control" name="description" rows="5"><?= e($prod['description'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="form-label">Ativo</label>
        <select class="form-select" name="is_active">
          <option value="1" <?= (($prod['is_active'] ?? 1)?'selected':'') ?>>Sim</option>
          <option value="0" <?= (($prod['is_active'] ?? 1)?'':'selected') ?>>Não</option>
        </select>
      </div>
      <div>
        <label class="form-label">Destaque</label>
        <select class="form-select" name="featured">
          <option value="0" <?= (($prod['featured'] ?? 0)?'':'selected') ?>>Não</option>
          <option value="1" <?= (($prod['featured'] ?? 0)?'selected':'') ?>>Sim</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <button class="btn btn-primary">Salvar</button>
        <a class="btn btn-outline-secondary" href="/admin/produtos">Cancelar</a>
      </div>
    </form>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php';



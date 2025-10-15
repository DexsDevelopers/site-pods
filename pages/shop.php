<?php
$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$sql = "SELECT p.id, p.name, p.slug, p.price, p.cover_image FROM products p WHERE p.is_active = 1";
$params = [];
if ($q !== '') { $sql .= " AND p.name LIKE :q"; $params[':q'] = "%$q%"; }
if ($cat) { $sql .= " AND p.category_id = :cat"; $params[':cat'] = $cat; }
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$cats = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
?>
<section class="py-12">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <h1 class="text-3xl font-bold">Loja</h1>
      <form class="flex items-center gap-2">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Buscar produtos" class="form-control" />
        <select name="cat" class="form-select">
          <option value="0">Todas categorias</option>
          <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $cat===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary">Filtrar</button>
      </form>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <?php foreach ($stmt as $prod): ?>
      <div class="rounded-3xl p-4 card-glass dark:card-glass-dark shadow-glass product-card" data-aos="fade-up">
        <a href="/produto/<?= e($prod['slug']) ?>" class="block">
          <img class="rounded-2xl w-full h-48 object-cover" src="<?= e($prod['cover_image']) ?>" alt="<?= e($prod['name']) ?>">
          <div class="mt-4 flex items-start justify-between gap-2">
            <div>
              <h3 class="font-semibold leading-tight"><?= e($prod['name']) ?></h3>
              <p class="opacity-70"><?= money((float)$prod['price']) ?></p>
            </div>
            <button type="button" onclick="addToCart(<?= (int)$prod['id'] ?>)" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-primary-600 text-white hover:bg-primary-500">
              <i class="fa-solid fa-cart-plus"></i>
              <span>Adicionar</span>
            </button>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


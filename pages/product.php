<?php
$slug = $params['slug'] ?? '';
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = :slug AND p.is_active = 1 LIMIT 1");
$stmt->execute([':slug' => $slug]);
$product = $stmt->fetch();
if (!$product) { http_response_code(404); echo '<div class="max-w-4xl mx-auto px-4 py-16"><h1 class="text-3xl font-bold">Produto não encontrado</h1></div>'; return; }
?>
<section class="py-12">
  <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-10 items-start">
    <div data-aos="zoom-in">
      <img class="rounded-3xl w-full object-cover shadow-glass" src="<?= e($product['cover_image']) ?>" alt="<?= e($product['name']) ?>">
    </div>
    <div data-aos="fade-left">
      <h1 class="text-3xl md:text-4xl font-bold"><?= e($product['name']) ?></h1>
      <p class="opacity-70 mt-1">Categoria: <?= e($product['category_name'] ?? '—') ?></p>
      <p class="text-3xl mt-4 font-extrabold text-primary-600"><?= money((float)$product['price']) ?></p>
      <p class="mt-4 opacity-90 leading-relaxed"><?= nl2br(e($product['description'] ?? '')) ?></p>
      <div class="mt-6 flex items-center gap-3">
        <button class="btn btn-lg btn-primary gradient-brand border-0 text-white" onclick="addToCart(<?= (int)$product['id'] ?>)">
          <i class="fa-solid fa-cart-plus me-2"></i>Adicionar ao carrinho
        </button>
        <a href="/loja" class="btn btn-lg btn-outline-light">Voltar</a>
      </div>
    </div>
  </div>
</section>


<?php
$cart = $_SESSION['cart'] ?? [];
// Fetch products for IDs in cart
$ids = array_keys($cart);
$items = [];
$total = 0.0;
if ($ids) {
  $in = implode(',', array_fill(0, count($ids), '?'));
  $stmt = $pdo->prepare("SELECT id, name, price, cover_image, slug FROM products WHERE id IN ($in)");
  $stmt->execute($ids);
  $rows = $stmt->fetchAll();
  foreach ($rows as $row) {
    $qty = (int)($cart[$row['id']] ?? 0);
    $sub = (float)$row['price'] * $qty;
    $items[] = ['p' => $row, 'qty' => $qty, 'sub' => $sub];
    $total += $sub;
  }
}
?>
<section class="py-12">
  <div class="max-w-6xl mx-auto px-4">
    <h1 class="text-3xl font-bold mb-6">Carrinho</h1>
    <?php if (!$items): ?>
      <div class="p-6 rounded-2xl card-glass dark:card-glass-dark">Seu carrinho está vazio.</div>
    <?php else: ?>
      <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 flex flex-col gap-4">
          <?php foreach ($items as $it): $p=$it['p']; ?>
          <div class="p-4 rounded-2xl card-glass dark:card-glass-dark flex items-center gap-4">
            <img class="w-24 h-24 rounded-xl object-cover" src="<?= e($p['cover_image']) ?>" alt="<?= e($p['name']) ?>">
            <div class="flex-1">
              <a href="/produto/<?= e($p['slug']) ?>" class="font-semibold hover:text-primary-600"><?= e($p['name']) ?></a>
              <div class="opacity-70"><?= money((float)$p['price']) ?></div>
            </div>
            <form method="post" action="/api/cart" class="flex items-center gap-2" onsubmit="return false;">
              <input type="number" min="1" value="<?= (int)$it['qty'] ?>" class="form-control w-24" onchange="updateQty(<?= (int)$p['id'] ?>, this.value)">
              <button class="btn btn-outline-danger" onclick="removeItem(<?= (int)$p['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
            </form>
            <div class="font-semibold w-28 text-right"><?= money((float)$it['sub']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div>
          <div class="p-6 rounded-2xl card-glass dark:card-glass-dark">
            <h2 class="text-xl font-bold mb-4">Resumo</h2>
            <div class="flex items-center justify-between">
              <span>Total</span>
              <span class="text-2xl font-extrabold text-primary-600"><?= money($total) ?></span>
            </div>
            <a href="/checkout" class="btn btn-lg btn-primary w-full mt-4">Ir para o checkout</a>
          </div>
        </div>
      </div>
      <script>
        async function updateQty(id, qty) {
          const res = await fetch('/api/cart', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'set', productId:id, quantity: parseInt(qty,10)||1 }) });
          location.reload();
        }
        async function removeItem(id) {
          const res = await fetch('/api/cart', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'remove', productId:id }) });
          location.reload();
        }
      </script>
    <?php endif; ?>
  </div>
</section>


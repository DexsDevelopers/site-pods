<?php
$cart = $_SESSION['cart'] ?? [];
if (!$cart) { echo '<div class="max-w-4xl mx-auto px-4 py-16">Carrinho vazio.</div>'; return; }

// compute total
$ids = array_keys($cart);
$in = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($in)");
$stmt->execute($ids);
$total = 0.0; $items = [];
foreach ($stmt as $row) { $qty = (int)$cart[$row['id']]; $sub = $qty * (float)$row['price']; $total += $sub; $items[] = [$row, $qty, $sub]; }
?>
<section class="py-12">
  <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-8">
    <div>
      <h1 class="text-2xl font-bold mb-4">Finalizar compra</h1>
      <form id="checkoutForm" class="space-y-3">
        <input class="form-control" name="name" placeholder="Nome completo" required>
        <input class="form-control" name="email" type="email" placeholder="E-mail" required>
        <input class="form-control" name="phone" placeholder="Telefone" required>
        <input class="form-control" name="address" placeholder="Endereço" required>
        <button class="btn btn-primary w-full">Confirmar pedido</button>
      </form>
    </div>
    <div>
      <div class="p-6 rounded-2xl card-glass dark:card-glass-dark">
        <h2 class="text-xl font-bold mb-4">Resumo</h2>
        <ul class="space-y-2">
          <?php foreach ($items as [$p,$q,$s]): ?>
            <li class="flex items-center justify-between">
              <span><?= e($p['name']) ?> × <?= (int)$q ?></span>
              <span><?= money($s) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="flex items-center justify-between mt-4">
          <span>Total</span>
          <span class="text-2xl font-extrabold text-primary-600"><?= money($total) ?></span>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
document.getElementById('checkoutForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  const payload = Object.fromEntries(fd.entries());
  const res = await fetch('/api/checkout', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
  const data = await res.json();
  if (data.success) {
    localStorage.setItem('cartCount', '0');
    location.href = '/sucesso';
  } else {
    Swal.fire({ icon:'error', title:'Falha no pedido', text: data.message || 'Tente novamente.' });
  }
});
</script>


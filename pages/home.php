<section class="relative overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 py-16">
    <div class="grid md:grid-cols-2 gap-10 items-center">
      <div data-aos="fade-right">
        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
          Pods eletrônicos Cirrago<br>
          <span class="text-transparent bg-clip-text gradient-brand">Sabor e tecnologia</span>
        </h1>
        <p class="mt-4 text-lg opacity-80">Experiência premium, autonomia e sabores intensos. Design moderno e portátil.</p>
        <div class="mt-6 flex gap-3">
          <a href="/loja" class="btn btn-lg btn-primary gradient-brand border-0 text-white shadow-glass">Comprar agora</a>
          <a href="#destaques" class="btn btn-lg btn-outline-light">Ver destaques</a>
        </div>
      </div>
      <div class="relative" data-aos="fade-left">
        <div class="rounded-3xl card-glass dark:card-glass-dark p-6 shadow-glass">
          <img src="https://images.unsplash.com/photo-1615634260167-c1013f4c6f75?q=80&w=1200&auto=format&fit=crop" alt="Pod eletrônico" class="rounded-2xl w-full object-cover">
        </div>
      </div>
    </div>
  </div>
</section>

<section id="destaques" class="py-16 bg-white/40 dark:bg-black/20">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-end justify-between mb-8">
      <h2 class="text-2xl md:text-3xl font-bold">Destaques</h2>
      <a href="/loja" class="text-primary-600 hover:underline">Ver loja</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $stmt = $pdo->query("SELECT p.id, p.name, p.slug, p.price, p.cover_image FROM products p WHERE p.is_active = 1 ORDER BY p.featured DESC, p.created_at DESC LIMIT 6");
      foreach ($stmt as $prod):
      ?>
      <div class="rounded-3xl p-4 card-glass dark:card-glass-dark shadow-glass product-card" data-aos="fade-up">
        <a href="/produto/<?= e($prod['slug']) ?>" class="block">
          <img class="rounded-2xl w-full h-52 object-cover" src="<?= e($prod['cover_image']) ?>" alt="<?= e($prod['name']) ?>">
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


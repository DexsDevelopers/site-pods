<?php $path = current_path(); ?>
<header class="sticky top-0 z-40 backdrop-blur bg-white/60 dark:bg-black/40 border-b border-white/10 shadow-glass">
  <div class="max-w-7xl mx-auto px-4">
    <nav class="flex items-center justify-between py-3">
      <a href="/" class="flex items-center gap-2 group">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-neu">
          <i class="fa-solid fa-bolt"></i>
        </span>
        <span class="font-semibold text-lg tracking-tight"><?= e(APP_NAME) ?></span>
      </a>
      <ul class="hidden md:flex items-center gap-6">
        <li><a class="hover:text-primary-400 transition-colors <?= $path==='/'?'text-primary-400':'' ?>" href="/">Início</a></li>
        <li><a class="hover:text-primary-400 transition-colors <?= $path==='/loja'?'text-primary-400':'' ?>" href="/loja">Loja</a></li>
        <li><a class="hover:text-primary-400 transition-colors" href="/checkout">Checkout</a></li>
      </ul>
      <div class="flex items-center gap-2">
        <a href="/carrinho" class="relative inline-flex items-center justify-center h-10 w-10 rounded-full bg-white/60 dark:bg-white/10 hover:bg-white/80 transition shadow-glass">
          <i class="fa-solid fa-cart-shopping"></i>
          <span id="cartCount" class="absolute -top-1 -right-1 text-xs px-1.5 py-0.5 rounded-full bg-primary-600 text-white">0</span>
        </a>
        <button class="md:hidden inline-flex items-center justify-center h-10 w-10 rounded-full bg-white/60 dark:bg-white/10 hover:bg-white/80 transition" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </nav>
  </div>
  <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="mobileMenuLabel">Menu</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="flex flex-col gap-4">
        <li><a class="hover:text-primary-600" href="/">Início</a></li>
        <li><a class="hover:text-primary-600" href="/loja">Loja</a></li>
        <li><a class="hover:text-primary-600" href="/checkout">Checkout</a></li>
        <li><button id="themeToggleMobile" class="btn btn-outline-dark">Alternar Tema</button></li>
      </ul>
    </div>
  </div>
</header>


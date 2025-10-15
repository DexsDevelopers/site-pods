  </main>
  <footer class="border-t border-white/10 backdrop-blur bg-white/60 dark:bg-black/40 text-sm">
    <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col md:flex-row items-center justify-between">
      <p class="opacity-80">© <?= date('Y') ?> <?= e(APP_NAME) ?>. Todos os direitos reservados.</p>
      <div class="flex items-center gap-3">
        <button id="themeToggle" class="btn btn-sm btn-outline-light bg-white/30 dark:bg-white/10 rounded-full px-3 py-1 shadow-glass">
          <i class="fa-solid fa-moon"></i>
          <span class="ms-2">Tema</span>
        </button>
      </div>
    </div>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
  AOS.init({ duration: 700, once: true, easing: 'ease-out' });
  if (window.lucide) lucide.createIcons();
  const toggle = document.getElementById('themeToggle');
  toggle?.addEventListener('click', () => {
    const root = document.documentElement;
    const isDark = root.getAttribute('data-theme') === 'dark';
    const next = isDark ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    document.cookie = 'theme=' + next + '; path=/; max-age=' + (60*60*24*365);
  });
</script>
<script src="/assets/js/app.js"></script>
</body>
</html>


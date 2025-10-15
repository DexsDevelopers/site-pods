document.addEventListener('DOMContentLoaded', () => {
  // Update cart count from localStorage mirror or dataset
  const badge = document.getElementById('cartCount');
  try {
    const count = parseInt(localStorage.getItem('cartCount') || '0', 10);
    if (badge && !Number.isNaN(count)) badge.textContent = String(count);
  } catch (_e) {}

  const mobileToggle = document.getElementById('themeToggleMobile');
  const toggle = document.getElementById('themeToggle');
  const applyTheme = (next) => {
    document.documentElement.setAttribute('data-theme', next);
    document.cookie = 'theme=' + next + '; path=/; max-age=' + (60*60*24*365);
  };
  const handler = () => {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    applyTheme(isDark ? 'light' : 'dark');
  };
  toggle?.addEventListener('click', handler);
  mobileToggle?.addEventListener('click', handler);

  // Microinteractions with GSAP
  if (window.gsap) {
    const tl = gsap.timeline({ defaults: { ease: 'power2.out', duration: .5 } });
    tl.from('header', { y: -20, opacity: 0 })
      .from('main', { y: 10, opacity: 0 }, '-=0.2');
  }
});

// Add to cart utility for buttons with [data-add-to-cart]
async function addToCart(productId, quantity = 1) {
  const res = await fetch('/api/cart', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'add', productId, quantity })
  });
  const data = await res.json();
  if (data.success) {
    localStorage.setItem('cartCount', String(data.count || 0));
    const badge = document.getElementById('cartCount');
    if (badge) badge.textContent = String(data.count || 0);
    Swal.fire({ icon: 'success', title: 'Adicionado ao carrinho', timer: 1500, showConfirmButton: false });
  } else {
    Swal.fire({ icon: 'error', title: 'Não foi possível adicionar', text: data.message || 'Tente novamente.' });
  }
}

window.addToCart = addToCart;



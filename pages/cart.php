<?php
ob_start();
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

$carrinho = [];
$total = 0;
$subtotal = 0;
$taxa = 0;

// Simular carrinho vazio para demonstração
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    setcookie('carrinho', '', time() - 3600);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - Wazzy Pods</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100" style="background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 100%);">
    <!-- Header Simples Carrinho -->
    <header class="bg-slate-950 border-b border-purple-800/30 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/" class="flex items-center gap-2 group">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center group-hover:shadow-lg transition">
                    <i class="fas fa-skull-crossbones text-white text-lg"></i>
                </div>
                <div>
                    <div class="font-black text-lg gradient-text">WAZZY</div>
                    <div class="text-xs font-bold text-slate-400 leading-none">PODS</div>
                </div>
            </a>
            <h2 class="text-xl font-bold gradient-text">Carrinho de Compras</h2>
            <a href="/" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg text-white font-bold hover:shadow-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Continuar
            </a>
        </div>
    </header>

    <div class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-5xl font-black mb-8"><span style="background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Carrinho de Compras</span></h1>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Produtos no Carrinho -->
                <div class="lg:col-span-2">
                    <div id="cart-items" class="space-y-4">
                        <!-- Itens do carrinho aparecem aqui -->
                    </div>
                    
                    <!-- Carrinho Vazio -->
                    <div id="empty-cart" class="text-center py-12 glass rounded-xl p-8 border border-purple-800/30">
                        <i class="fas fa-shopping-cart text-6xl text-slate-600 mb-4"></i>
                        <h2 class="text-2xl font-bold mb-2">Seu carrinho está vazio</h2>
                        <p class="text-slate-400 mb-6">Adicione produtos para começar suas compras</p>
                        <a href="/" class="inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-white hover:shadow-lg transition">
                            <i class="fas fa-arrow-left mr-2"></i> Continuar Comprando
                        </a>
                    </div>
                </div>

                <!-- Resumo do Pedido -->
                <div id="order-summary" class="glass rounded-xl p-6 border border-purple-800/30 h-fit sticky top-32">
                    <h3 class="text-2xl font-bold gradient-text mb-6">Resumo do Pedido</h3>
                    
                    <div class="space-y-4 mb-6 pb-6 border-b border-slate-700">
                        <div class="flex justify-between text-slate-300">
                            <span>Subtotal:</span>
                            <span id="subtotal-value">R$ 0,00</span>
                        </div>
                        <div class="flex justify-between text-slate-300">
                            <span>Frete:</span>
                            <span id="shipping-value">Cálcular</span>
                        </div>
                        <div class="flex justify-between text-slate-300">
                            <span>Impostos:</span>
                            <span id="tax-value">R$ 0,00</span>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between text-xl font-bold">
                            <span>Total:</span>
                            <span id="total-value" class="gradient-text">R$ 0,00</span>
                        </div>
                    </div>

                    <button onclick="checkout()" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-white hover:shadow-lg transition mb-3">
                        <i class="fas fa-credit-card mr-2"></i> Ir para Checkout
                    </button>

                    <button onclick="continueShopping()" class="w-full py-3 glass rounded-lg font-bold text-purple-300 border border-purple-800/50 hover:bg-slate-800/50 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Continuar Comprando
                    </button>

                    <!-- Cupom -->
                    <div class="mt-6 pt-6 border-t border-slate-700">
                        <p class="text-sm text-slate-400 mb-2">Tem um cupom?</p>
                        <div class="flex gap-2">
                            <input type="text" id="coupon-code" placeholder="Código" class="flex-1 px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-slate-100 text-sm focus:outline-none focus:border-purple-600">
                            <button onclick="applyCoupon()" class="px-4 py-2 bg-purple-600/20 border border-purple-600/50 rounded-lg font-bold text-purple-400 hover:bg-purple-600/30 transition">
                                Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Simples Carrinho -->
    <footer class="bg-slate-950 border-t border-purple-800/30 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8 text-center text-slate-400">
            <p>&copy; 2024 Wazzy Pods. Todos os direitos reservados.</p>
        </div>
    </footer>

    <style>
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
        }

        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <script>
        // ========== CLASSE CART COMPLETA ==========
        class Cart {
            constructor() {
                this.items = this.loadFromStorage();
                this.render();
            }

            loadFromStorage() {
                try {
                    const data = localStorage.getItem('cart');
                    return data ? JSON.parse(data) : [];
                } catch (e) {
                    console.error('Erro ao carregar carrinho:', e);
                    return [];
                }
            }

            saveToStorage() {
                localStorage.setItem('cart', JSON.stringify(this.items));
                this.updateBadge();
            }

            add(product) {
                const existing = this.items.find(i => i.id === product.id);
                if (existing) {
                    existing.quantity += product.quantity || 1;
                } else {
                    product.quantity = product.quantity || 1;
                    this.items.push(product);
                }
                this.saveToStorage();
                this.render();
            }

            remove(productId) {
                this.items = this.items.filter(i => i.id !== productId);
                this.saveToStorage();
                this.render();
            }

            updateQuantity(productId, quantity) {
                const item = this.items.find(i => i.id === productId);
                if (item) {
                    const newQty = Math.max(1, parseInt(quantity) || 1);
                    if (newQty === 0) {
                        this.remove(productId);
                    } else {
                        item.quantity = newQty;
                        this.saveToStorage();
                        this.render();
                    }
                }
            }

            getSubtotal() {
                return this.items.reduce((total, item) => total + ((item.preco || item.preco_final || 0) * (item.quantity || item.qty || 0)), 0);
            }

            getTax() {
                return 0; // Remover taxa automática
            }

            getTotal() {
                return this.getSubtotal(); // Total = subtotal (sem taxa)
            }

            clear() {
                this.items = [];
                this.saveToStorage();
                this.render();
            }

            isEmpty() {
                return this.items.length === 0;
            }

            updateBadge() {
                const count = this.items.reduce((sum, item) => sum + item.quantity, 0);
                const badges = document.querySelectorAll('#cart-count, #cart-count-mobile');
                badges.forEach(b => b.textContent = count);
            }

            render() {
                const container = document.getElementById('cart-items');
                const emptyCart = document.getElementById('empty-cart');
                const orderSummary = document.getElementById('order-summary');

                if (this.isEmpty()) {
                    container.style.display = 'none';
                    emptyCart.style.display = 'block';
                    orderSummary.style.display = 'none';
                } else {
                    container.style.display = 'block';
                    emptyCart.style.display = 'none';
                    orderSummary.style.display = 'block';

                    container.innerHTML = this.items.map(item => `
                        <div class="glass rounded-xl p-6 border border-purple-800/30 flex gap-4 hover:border-purple-600/50 transition">
                            <img src="${item.imagem || 'https://via.placeholder.com/100'}" 
                                 alt="${item.nome}" 
                                 class="w-24 h-24 object-cover rounded-lg bg-slate-800">
                            
                            <div class="flex-1">
                                <h3 class="text-lg font-bold mb-1">${item.nome}</h3>
                                <p class="text-sm text-slate-400 mb-3">${item.categoria_nome || 'Sem categoria'}</p>
                                
                                <div class="flex items-center gap-2">
                                    <button onclick="cart.updateQuantity(${item.id}, ${item.quantity - 1})" 
                                            class="px-2 py-1 bg-slate-800/50 border border-purple-800/30 rounded hover:bg-slate-700 transition">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <input type="number" 
                                           value="${item.quantity}" 
                                           min="1" 
                                           max="999"
                                           onchange="cart.updateQuantity(${item.id}, this.value)" 
                                           class="w-14 text-center bg-slate-800/50 border border-purple-800/30 rounded py-1 text-sm">
                                    <button onclick="cart.updateQuantity(${item.id}, ${item.quantity + 1})" 
                                            class="px-2 py-1 bg-slate-800/50 border border-purple-800/30 rounded hover:bg-slate-700 transition">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="text-right flex flex-col justify-between">
                                <div>
                                    <p class="text-2xl font-bold gradient-text">R$ ${((item.preco || item.preco_final || 0) * (item.quantity || item.qty || 0)).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                                    <p class="text-xs text-slate-500">Un: R$ ${(item.preco || item.preco_final || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                                </div>
                                <button onclick="cart.remove(${item.id})" 
                                        class="px-4 py-2 bg-red-600/20 border border-red-600/50 rounded text-red-400 hover:bg-red-600/30 transition text-sm font-medium">
                                    <i class="fas fa-trash mr-2"></i> Remover
                                </button>
                            </div>
                        </div>
                    `).join('');

                    // Atualizar resumo
                    const subtotal = this.getSubtotal();
                    const tax = this.getTax();
                    const total = this.getTotal();

                    document.getElementById('subtotal-value').textContent = `R$ ${subtotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                    document.getElementById('tax-value').textContent = `R$ ${tax.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                    document.getElementById('total-value').textContent = `R$ ${total.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                }

                this.updateBadge();
            }
        }

        // Inicializar carrinho
        const cart = new Cart();

        // Funções globais
        function addToCart(id, nome, preco) {
            const item = {
                id: id,
                nome: nome,
                preco: parseFloat(preco),
                preco_final: parseFloat(preco), // Mantém compatibilidade
                quantity: 1,
                imagem: 'https://via.placeholder.com/100',
                categoria_nome: 'Pods'
            };
            cart.add(item);
            
            // Notificação
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Adicionado!',
                    html: '<strong>' + nome + '</strong><br>foi adicionado ao carrinho',
                    timer: 2000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true,
                    background: 'rgba(15, 23, 42, 0.95)',
                    didOpen: (toast) => {
                        toast.style.color = '#e2e8f0';
                    }
                });
            }
        }

        function updateCartBadge() {
            cart.updateBadge();
        }

        function checkout() {
            if (cart.isEmpty()) {
                alert('Seu carrinho está vazio!');
                return;
            }
            // Salvar carrinho em cookie para o checkout
            document.cookie = 'cart=' + JSON.stringify(cart.items) + '; path=/';
            window.location.href = 'checkout.php';
        }

        function continueShopping() {
            window.location.href = '/';
        }

        function applyCoupon() {
            const code = document.getElementById('coupon-code').value.trim();
            if (!code) {
                alert('Digite um código de cupom!');
                return;
            }
            alert('Cupom "' + code + '" - em desenvolvimento!');
        }

        // Sincronizar badge ao carregar a página
        updateCartBadge();
    </script>
</body>
</html>

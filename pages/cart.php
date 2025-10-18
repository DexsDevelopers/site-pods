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
    <?php include '../header.php'; ?>

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

    <?php include '../footer.php'; ?>

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
        // Sistema de Carrinho com localStorage
        class Cart {
            constructor() {
                this.items = JSON.parse(localStorage.getItem('cart') || '[]');
                this.render();
            }

            add(product) {
                const existing = this.items.find(i => i.id === product.id);
                if (existing) {
                    existing.quantity += product.quantity || 1;
                } else {
                    product.quantity = product.quantity || 1;
                    this.items.push(product);
                }
                this.save();
            }

            remove(productId) {
                this.items = this.items.filter(i => i.id !== productId);
                this.save();
            }

            updateQuantity(productId, quantity) {
                const item = this.items.find(i => i.id === productId);
                if (item) {
                    item.quantity = Math.max(1, quantity);
                    if (item.quantity === 0) this.remove(productId);
                    else this.save();
                }
            }

            getTotal() {
                return this.items.reduce((total, item) => total + (item.preco_final * item.quantity), 0);
            }

            clear() {
                this.items = [];
                this.save();
            }

            save() {
                localStorage.setItem('cart', JSON.stringify(this.items));
                this.render();
                updateCartBadge();
            }

            render() {
                const container = document.getElementById('cart-items');
                const empty = document.getElementById('empty-cart');
                const summary = document.getElementById('order-summary');

                if (this.items.length === 0) {
                    container.style.display = 'none';
                    empty.style.display = 'block';
                    summary.style.display = 'none';
                } else {
                    container.style.display = 'block';
                    empty.style.display = 'none';
                    summary.style.display = 'block';
                    
                    container.innerHTML = this.items.map(item => `
                        <div class="glass rounded-xl p-6 border border-purple-800/30 flex gap-4">
                            <img src="${item.imagem || 'https://via.placeholder.com/100'}" alt="${item.nome}" class="w-24 h-24 object-cover rounded-lg">
                            
                            <div class="flex-1">
                                <h3 class="text-xl font-bold mb-1">${item.nome}</h3>
                                <p class="text-sm text-slate-400 mb-2">${item.categoria_nome || 'Sem categoria'}</p>
                                
                                <div class="flex items-center gap-2">
                                    <button onclick="cart.updateQuantity(${item.id}, ${item.quantity - 1})" class="px-3 py-1 bg-slate-800/50 border border-purple-800/30 rounded hover:bg-slate-800">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <input type="number" value="${item.quantity}" min="1" onchange="cart.updateQuantity(${item.id}, parseInt(this.value))" class="w-12 text-center bg-slate-800/50 border border-purple-800/30 rounded py-1">
                                    <button onclick="cart.updateQuantity(${item.id}, ${item.quantity + 1})" class="px-3 py-1 bg-slate-800/50 border border-purple-800/30 rounded hover:bg-slate-800">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-2xl font-bold gradient-text mb-2">R$ ${(item.preco_final * item.quantity).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                                <button onclick="cart.remove(${item.id})" class="px-4 py-2 bg-red-600/20 border border-red-600/50 rounded text-red-400 hover:bg-red-600/30 transition">
                                    <i class="fas fa-trash mr-2"></i> Remover
                                </button>
                            </div>
                        </div>
                    `).join('');

                    // Atualizar resumo
                    const subtotal = this.getTotal();
                    const tax = subtotal * 0.08;
                    const total = subtotal + tax;

                    document.getElementById('subtotal-value').textContent = `R$ ${subtotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                    document.getElementById('tax-value').textContent = `R$ ${tax.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                    document.getElementById('total-value').textContent = `R$ ${total.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                }
            }
        }

        const cart = new Cart();

        function addToCart(id, nome, preco) {
            const item = {
                id,
                nome,
                preco_final: preco,
                quantity: 1,
                imagem: 'https://via.placeholder.com/100'
            };
            cart.add(item);
            alert('Produto adicionado ao carrinho!');
        }

        function updateCartBadge() {
            const count = cart.items.length;
            const badges = document.querySelectorAll('#cart-count, #cart-count-mobile');
            badges.forEach(b => b.textContent = count);
        }

        function checkout() {
            alert('Sistema de checkout em desenvolvimento!');
        }

        function continueShopping() {
            window.location.href = '/';
        }

        function applyCoupon() {
            alert('Sistema de cupons em desenvolvimento!');
        }

        updateCartBadge();
    </script>
</body>
</html>

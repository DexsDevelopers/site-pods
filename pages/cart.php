<?php
require_once '../includes/config.php';
require_once '../includes/helpers.php';

$cart = json_decode($_COOKIE['cart'] ?? '[]', true);
$total = array_sum(array_map(fn($item) => $item['preco'] * $item['qty'], $cart ?? []));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - TechVapor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); }
        .glass { background: rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(147, 51, 234, 0.2); }
    </style>
</head>
<body class="text-slate-100 pt-20">

    <div class="max-w-7xl mx-auto px-4 py-16">
        <h1 class="text-4xl font-black mb-12">🛒 Seu Carrinho</h1>

        <div class="grid md:grid-cols-3 gap-12">
            <!-- Itens do Carrinho -->
            <div class="md:col-span-2">
                <div id="cartItems" class="space-y-6">
                    <!-- Itens renderizados aqui -->
                </div>
                <a href="../index.php" class="mt-6 block px-6 py-3 glass rounded-lg text-center hover:bg-white/10 transition">
                    ← Continuar Comprando
                </a>
            </div>

            <!-- Resumo -->
            <div class="glass rounded-lg p-8 h-fit sticky top-24">
                <h2 class="text-2xl font-black mb-6">Resumo do Pedido</h2>
                
                <div class="space-y-4 mb-6 pb-6 border-b border-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span id="subtotal">R$ 0,00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Frete</span>
                        <span class="text-green-400">Grátis</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Desconto (10%)</span>
                        <span id="desconto" class="text-green-400">-R$ 0,00</span>
                    </div>
                </div>

                <div class="flex justify-between text-xl font-black mb-6">
                    <span>Total</span>
                    <span class="gradient-text" id="total">R$ 0,00</span>
                </div>

                <button onclick="checkout()" class="w-full py-4 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:shadow-lg transition mb-3">
                    Ir para o Checkout
                </button>
                <button onclick="applyCoupon()" class="w-full py-3 glass rounded-lg font-bold hover:bg-white/20 transition">
                    Aplicar Cupom
                </button>
            </div>
        </div>
    </div>

    <script>
        function loadCart() {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            let html = '';
            let subtotal = 0;

            if (cart.length === 0) {
                html = '<div class="glass p-8 rounded-lg text-center"><p class="text-lg mb-4">Seu carrinho está vazio</p><a href="../index.php" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold inline-block">Voltar à Loja</a></div>';
            } else {
                cart.forEach((item, index) => {
                    subtotal += item.preco * item.qty;
                    html += `
                        <div class="glass p-6 rounded-lg flex gap-6">
                            <img src="${item.imagem}" class="w-24 h-24 rounded object-cover" alt="">
                            <div class="flex-1">
                                <h3 class="font-bold mb-2">${item.nome}</h3>
                                <p class="text-slate-400 mb-4">R$ ${item.preco.toFixed(2).replace('.', ',')}</p>
                                <div class="flex items-center gap-2">
                                    <button onclick="updateQty(${index}, ${item.qty - 1})" class="px-3 py-1 glass rounded hover:bg-white/10">−</button>
                                    <span class="px-4">${item.qty}x</span>
                                    <button onclick="updateQty(${index}, ${item.qty + 1})" class="px-3 py-1 glass rounded hover:bg-white/10">+</button>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold mb-4">R$ ${(item.preco * item.qty).toFixed(2).replace('.', ',')}</p>
                                <button onclick="removeItem(${index})" class="px-3 py-2 bg-red-600/20 text-red-400 rounded hover:bg-red-600/40 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
            }

            document.getElementById('cartItems').innerHTML = html;
            const desconto = subtotal * 0.1;
            const total = subtotal - desconto;

            document.getElementById('subtotal').textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
            document.getElementById('desconto').textContent = '-R$ ' + desconto.toFixed(2).replace('.', ',');
            document.getElementById('total').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
        }

        function removeItem(index) {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }

        function updateQty(index, qty) {
            if (qty <= 0) return removeItem(index);
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            cart[index].qty = qty;
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }

        function checkout() {
            alert('✅ Redirecionando para pagamento...');
            window.location.href = 'checkout.php';
        }

        function applyCoupon() {
            const coupon = prompt('Digite seu cupom:');
            if (coupon === 'WELCOME10') {
                alert('✅ Cupom aplicado com sucesso!');
            } else {
                alert('❌ Cupom inválido');
            }
        }

        loadCart();
    </script>

</body>
</html>

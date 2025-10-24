<?php
echo "<h1>🔍 Debug do Carrinho</h1>";

// Verificar cookies
echo "<h2>Cookies:</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

// Verificar carrinho específico
if (isset($_COOKIE['cart'])) {
    echo "<h2>Carrinho (JSON):</h2>";
    echo "<pre>";
    echo htmlspecialchars($_COOKIE['cart']);
    echo "</pre>";
    
    echo "<h2>Carrinho (Decodificado):</h2>";
    $cart = json_decode($_COOKIE['cart'], true);
    echo "<pre>";
    print_r($cart);
    echo "</pre>";
    
    if ($cart) {
        echo "<h2>Cálculos:</h2>";
        foreach ($cart as $item) {
            $preco = $item['preco'] ?? $item['preco_final'] ?? 0;
            $qty = $item['qty'] ?? $item['quantity'] ?? 0;
            $total = $preco * $qty;
            
            echo "<p><strong>Item:</strong> " . htmlspecialchars($item['nome'] ?? 'N/A') . "</p>";
            echo "<p><strong>Preço:</strong> R$ " . number_format($preco, 2, ',', '.') . "</p>";
            echo "<p><strong>Quantidade:</strong> " . $qty . "</p>";
            echo "<p><strong>Total:</strong> R$ " . number_format($total, 2, ',', '.') . "</p>";
            echo "<hr>";
        }
        
        $subtotal = 0;
        foreach ($cart as $item) {
            $preco = $item['preco'] ?? $item['preco_final'] ?? 0;
            $qty = $item['qty'] ?? $item['quantity'] ?? 0;
            $subtotal += $preco * $qty;
        }
        
        echo "<p><strong>Subtotal Total:</strong> R$ " . number_format($subtotal, 2, ',', '.') . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Cookie 'cart' não encontrado!</p>";
}

// Verificar localStorage via JavaScript
echo "<h2>JavaScript Debug:</h2>";
echo "<script>";
echo "console.log('🔍 Debug do Carrinho via JavaScript:');";
echo "console.log('localStorage.getItem(\"cart\"):', localStorage.getItem('cart'));";
echo "const cart = JSON.parse(localStorage.getItem('cart') || '[]');";
echo "console.log('Carrinho parseado:', cart);";
echo "if (cart.length > 0) {";
echo "  cart.forEach((item, index) => {";
echo "    console.log(`Item ${index + 1}:`, item);";
echo "    const preco = item.preco || item.preco_final || 0;";
echo "    const qty = item.qty || item.quantity || 0;";
echo "    const total = preco * qty;";
echo "    console.log(`  Preço: R$ ${preco}, Qty: ${qty}, Total: R$ ${total}`);";
echo "  });";
echo "} else {";
echo "  console.log('Carrinho vazio');";
echo "}";
echo "</script>";

echo "<p><strong>Instruções:</strong></p>";
echo "<ol>";
echo "<li>Abra o console do navegador (F12)</li>";
echo "<li>Veja os logs do JavaScript</li>";
echo "<li>Verifique se a quantidade está correta</li>";
echo "<li>Verifique se o preço está correto</li>";
echo "</ol>";
?>

<?php
echo "<h1>🔍 Teste da API Mercado Pago</h1>";

// Primeiro, criar um pedido de teste
require_once 'includes/config_hostinger.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=u853242961_loja_pods;charset=utf8mb4", 'u853242961_pods_saluc', 'Lucastav8012@', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Criar pedido de teste
    $stmt = $pdo->prepare("INSERT INTO orders (nome, email, telefone, cep, endereco, numero, complemento, bairro, cidade, estado, total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([
        'Teste API',
        'teste@teste.com',
        '11999999999',
        '00000000',
        'Rua Teste',
        '123',
        '',
        'Centro',
        'São Paulo',
        'SP',
        100
    ]);
    
    $orderId = $pdo->lastInsertId();
    echo "<p style='color: green;'>✅ Pedido de teste criado com ID: $orderId</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao criar pedido: " . $e->getMessage() . "</p>";
    exit;
}

// Dados de teste com pedido real
$testData = [
    'order_id' => $orderId,
    'items' => [
        [
            'nome' => 'IGNITE V50 (Prata)',
            'quantity' => 1,
            'preco' => 100
        ]
    ],
    'total' => 100
];

echo "<h2>Dados de teste:</h2>";
echo "<pre>";
print_r($testData);
echo "</pre>";

echo "<h2>Testando API...</h2>";

try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://maroon-louse-320109.hostingersite.com/api/mercadopago.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h3>Status HTTP: $httpCode</h3>";
    echo "<h3>Resposta:</h3>";
    echo "<pre>";
    echo htmlspecialchars($response);
    echo "</pre>";
    
    $result = json_decode($response, true);
    echo "<h3>JSON Decodificado:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result && isset($result['success']) && $result['success']) {
        echo "<p style='color: green;'>✅ API funcionando corretamente!</p>";
    } else {
        echo "<p style='color: red;'>❌ API com problema</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>

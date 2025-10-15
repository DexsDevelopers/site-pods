<?php
// Inclui o cabeçalho
include 'header.php';

// Simulação de dados de produtos (em um projeto real, viria de um Banco de Dados)
$produtos = [
    [
        'nome' => 'Vapor Premium X-01',
        'descricao' => 'Design elegante, alta performance e bateria de longa duração.',
        'preco' => 'R$ 299,90',
        'imagem' => 'produto_x01.jpg' // Substitua por imagens reais
    ],
    [
        'nome' => 'Acessório Ultra Slim',
        'descricao' => 'Case de proteção minimalista e resistente para seu dispositivo.',
        'preco' => 'R$ 89,90',
        'imagem' => 'acessorio_slim.jpg'
    ],
    // Adicione mais produtos aqui
];
?>

<main class="content">
    <section class="hero-banner">
        <div class="container">
            <h1>A Revolução da Tecnologia Pessoal</h1>
            <p>Explore nossa linha de produtos com design minimalista e performance superior.</p>
            <a href="#" class="cta-button">Ver Produtos</a>
        </div>
    </section>

    <section class="product-grid">
        <div class="container">
            <h2>Nossos Destaques</h2>
            <div class="grid">
                <?php foreach ($produtos as $produto) : ?>
                    <div class="product-card">
                        <img src="imagens/<?php echo htmlspecialchars($produto['imagem']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                        <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <p><?php echo htmlspecialchars($produto['descricao']); ?></p>
                        <span class="price"><?php echo htmlspecialchars($produto['preco']); ?></span>
                        <a href="#" class="add-to-cart">Detalhes</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            </div>
    </section>
</main>

<?php
// Inclui o rodapé
include 'footer.php';
?>
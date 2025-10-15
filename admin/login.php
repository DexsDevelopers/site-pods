<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db_connect.php';

if (!empty($_SESSION['admin'])) { redirect('/admin'); }

if (is_post()) {
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE (email = :e OR name = :e) AND is_admin = 1 AND is_active = 1 LIMIT 1');
  $stmt->execute([':e'=>$email]);
  $u = $stmt->fetch();
  if ($u && password_verify($pass, $u['password_hash'])) {
    $_SESSION['admin'] = ['id'=>$u['id'], 'name'=>$u['name'], 'email'=>$u['email']];
    redirect('/admin');
  } else {
    $error = 'Credenciais inválidas';
  }
}

$pageTitle = 'Admin · Login';
include __DIR__ . '/../templates/header.php';
?>
<section class="py-24">
  <div class="max-w-md mx-auto px-4">
    <div class="p-6 rounded-3xl card-glass dark:card-glass-dark shadow-glass">
      <h1 class="text-2xl font-bold mb-4">Acesso administrativo</h1>
      <?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <form method="post" class="space-y-3">
        <input class="form-control" type="email" name="email" placeholder="E-mail" required>
        <input class="form-control" type="password" name="password" placeholder="Senha" required>
        <button class="btn btn-primary w-full">Entrar</button>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php';



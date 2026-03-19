<?php
require_once __DIR__ . "/app/config.php"; // carrega env + sessão + pdo + csrf funcs

if (!empty($_SESSION['user'])) {
  header("Location: /dashboard.php");
  exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();

  $email = trim($_POST['email'] ?? '');
  $password = (string)($_POST['password'] ?? '');

  $stmt = $pdo->prepare("SELECT id,name,email,role,password_hash FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password_hash'])) {
    session_regenerate_on_login();

    unset($user['password_hash']);
    $_SESSION['user'] = $user;

    header("Location: /dashboard.php");
    exit;
  } else {
    $error = "Email ou senha incorretos.";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Sistema de Frequência</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <div class="login-container">
    <div class="login-form">
      <h2>Sistema de Controle de Frequência</h2>

      <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <?= csrf_input() ?>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required>
        </div>
        <div class="form-group">
          <label>Senha</label>
          <input type="password" name="password" required>
        </div>
        <button class="btn btn-primary btn-block">Entrar</button>
      </form>
    </div>
  </div>
</body>
</html>
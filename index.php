<?php
require_once __DIR__ . "/app/config.php";

if (!empty($_SESSION['user'])) {
  header("Location: /dashboard.php");
  exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT id,name,email,role,password_hash FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password_hash'])) {
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
  <title>Sistema de Controle de Frequência</title>
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
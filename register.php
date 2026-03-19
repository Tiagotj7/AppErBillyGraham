<?php
require_once __DIR__ . "/app/config.php";

if (!empty($_SESSION['user'])) {
  header("Location: /dashboard.php");
  exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();

  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = (string)($_POST['password'] ?? '');
  $password2 = (string)($_POST['password2'] ?? '');

  if ($name === '' || $email === '' || $password === '' || $password2 === '') {
    $error = "Preencha todos os campos.";
  } elseif (mb_strlen($name) < 3) {
    $error = "Nome muito curto (mínimo 3 caracteres).";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Email inválido.";
  } elseif (strlen($password) < 6) {
    $error = "Senha muito curta (mínimo 6 caracteres).";
  } elseif ($password !== $password2) {
    $error = "As senhas não conferem.";
  } else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      $error = "Este email já está cadastrado.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'conselheiro')");
      $stmt->execute([$name, $email, $hash]);
      $success = "Cadastro realizado com sucesso! Faça login.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cadastro - Sistema de Frequência</title>
  <link rel="stylesheet" href="/assets/style.css">

  <link rel="shortcut icon" href="assets/img/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-16x16.png">
  <link rel="manifest" href="assets/img/site.webmanifest">

</head>

<body>
  <div class="login-container">
    <div class="login-form">

      <div class="login-logo">
        <img src="/assets/img/logo.svg" alt="Logo" class="login-logo-img">
      </div>

      <h2>Criar Conta</h2>

      <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <?= csrf_input() ?>
        <div class="form-group">
          <label>Nome</label>
          <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Senha</label>
          <input type="password" name="password" required>
        </div>
        <div class="form-group">
          <label>Confirmar Senha</label>
          <input type="password" name="password2" required>
        </div>

        <button class="btn btn-primary btn-block">Cadastrar</button>
      </form>

      <div style="margin-top:15px;text-align:center;">
        <a href="/index.php">Já tenho conta</a>
      </div>
    </div>
  </div>
</body>

</html>
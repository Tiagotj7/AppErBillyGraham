<?php
// /account.php
require_once __DIR__ . "/app/auth.php";
require_login();

$title = "Minha conta";
$activeTab = ""; // não marca nenhuma aba
$user = current_user();

$error = "";
$success = "";

/**
 * Trocar senha
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();

  $current = (string)($_POST['current_password'] ?? '');
  $new = (string)($_POST['new_password'] ?? '');
  $new2 = (string)($_POST['new_password2'] ?? '');

  if ($current === '' || $new === '' || $new2 === '') {
    $error = "Preencha todos os campos.";
  } elseif (strlen($new) < 6) {
    $error = "A nova senha deve ter no mínimo 6 caracteres.";
  } elseif ($new !== $new2) {
    $error = "A confirmação da nova senha não confere.";
  } else {
    // Busca hash atual no banco
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([(int)$user['id']]);
    $row = $stmt->fetch();

    if (!$row) {
      $error = "Usuário não encontrado.";
    } elseif (!password_verify($current, $row['password_hash'])) {
      $error = "Senha atual incorreta.";
    } else {
      $newHash = password_hash($new, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
      $stmt->execute([$newHash, (int)$user['id']]);

      // opcional: regenerar sessão por segurança
      session_regenerate_on_login();

      $success = "Senha alterada com sucesso.";
    }
  }
}

require_once __DIR__ . "/app/header.php";
?>

<div class="tab-content">
  <h3 class="form-title">Minha conta</h3>

  <div style="margin-bottom: 16px;">
    <div><strong>Nome:</strong> <?= htmlspecialchars($user['name'] ?? '') ?></div>
    <div><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></div>
    <div><strong>Perfil:</strong> <?= htmlspecialchars(($user['role'] ?? '') === 'admin' ? 'Administrador' : 'Conselheiro') ?></div>
  </div>

  <h3 class="form-title" style="margin-top: 10px;">Trocar senha</h3>

  <?php if ($error): ?>
    <div class="error-message"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="success-message"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off" style="max-width: 520px;">
    <?= csrf_input() ?>

    <div class="form-group">
      <label>Senha atual</label>
      <input type="password" name="current_password" required>
    </div>

    <div class="form-group">
      <label>Nova senha</label>
      <input type="password" name="new_password" required>
    </div>

    <div class="form-group">
      <label>Confirmar nova senha</label>
      <input type="password" name="new_password2" required>
    </div>

    <button class="btn btn-primary" type="submit">Salvar nova senha</button>
  </form>
</div>

<?php require_once __DIR__ . "/app/footer.php"; ?>
<?php
require_once __DIR__ . "/app/auth.php";
require_login();

$title = "Painel";
$user = current_user();

require_once __DIR__ . "/app/header.php";
?>

<div class="tab-content">
  <h3 class="form-title">Bem-vindo!</h3>
  <p>Use as abas acima para cadastrar pessoas, registrar frequência e consultar histórico.</p>

  <div style="margin-top:15px;">
    <p><strong>Usuário:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
    <p><strong>Perfil:</strong> <?= htmlspecialchars(($user['role'] ?? '') === 'admin' ? 'Administrador' : 'Conselheiro') ?></p>
  </div>
</div>

<?php require_once __DIR__ . "/app/footer.php"; ?>
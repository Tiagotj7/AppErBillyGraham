<?php
// header.php
// Requer: app/auth.php (para current_user())
if (!function_exists('current_user')) {
  require_once __DIR__ . "/auth.php";
}

$user = current_user();
$title = $title ?? "Sistema de Frequência";

// opcional: definir qual aba está ativa manualmente
// $activeTab = $activeTab ?? ''; // 'people' | 'attendance' | 'history'
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<?php if ($user): ?>
<header class="header">
  <div class="container header-content">
    <div class="logo">
      <!-- Se quiser logo:
      <img src="/src/er_logo.svg" alt="Logo" class="nav-logo">
      -->
      <span>Sistema de Frequência</span>
    </div>

    <div class="user-info">
      <span class="username">
        <?= htmlspecialchars(($user['role'] ?? '') === 'admin' ? 'Administrador' : 'Conselheiro') ?>
      </span>
      <a href="/logout.php" class="btn btn-logout">Sair</a>
    </div>
  </div>
</header>

<div class="container main-content">
  <div class="tabs">
    <a class="tab <?= (($activeTab ?? '') === 'people') ? 'active' : '' ?>" href="/people.php">Cadastro de Pessoas</a>
    <a class="tab <?= (($activeTab ?? '') === 'attendance') ? 'active' : '' ?>" href="/attendance.php">Registro de Frequência</a>
    <a class="tab <?= (($activeTab ?? '') === 'history') ? 'active' : '' ?>" href="/history.php">Histórico</a>
  </div>
<?php endif; ?>
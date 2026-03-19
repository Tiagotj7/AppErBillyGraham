<?php
declare(strict_types=1);

require_once __DIR__ . "/auth.php"; // já carrega config + sessão + security + env
$user = current_user();

$title = $title ?? "Sistema de Frequência";
$activeTab = $activeTab ?? "";
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
    <div class="logo"><span>Sistema de Frequência</span></div>
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
    <a class="tab <?= $activeTab === 'people' ? 'active' : '' ?>" href="/people.php">Cadastro de Pessoas</a>
    <a class="tab <?= $activeTab === 'attendance' ? 'active' : '' ?>" href="/attendance.php">Registro de Frequência</a>
    <a class="tab <?= $activeTab === 'history' ? 'active' : '' ?>" href="/history.php">Histórico</a>
  </div>
<?php endif; ?>
<?php

declare(strict_types=1);

require_once __DIR__ . "/auth.php";
$user = current_user();

$title = $title ?? "Sistema de Frequência";
$activeTab = $activeTab ?? ""; // people | attendance | history
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="/assets/style.css">

  <link rel="shortcut icon" href="/assets/img/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/img/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/assets/img/favicon-16x16.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/assets/img/favicon-192x192.png">

  <link rel="manifest" href="/manifest.json">


</head>

<body>

  <?php if ($user): ?>
    <header class="header">
      <div class="container header-content">
        <div class="logo">
          <img src="/assets/img/logo.svg" alt="Logo" class="header-logo">
          <span>Sistema de Frequência</span>
        </div>

        <div class="user-info">
          <span class="username">
            <?= htmlspecialchars($user['name'] ?? 'Usuário') ?>
            <small style="opacity:.9;font-weight:600;">
              (<?= htmlspecialchars(($user['role'] ?? '') === 'admin' ? 'Admin' : 'Conselheiro') ?>)
            </small>
          </span>

          <!-- ✅ Novo: Minha Conta (trocar senha) -->
          <a href="/account.php" class="btn btn-logout" style="margin-right:10px;">
            Minha conta
          </a>

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
    </div>
  <?php endif; ?>
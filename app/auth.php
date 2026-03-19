<?php
declare(strict_types=1);

require_once __DIR__ . "/config.php";

function require_login(): void {
  if (empty($_SESSION['user'])) {
    header("Location: /index.php");
    exit;
  }
}

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function is_admin(): bool {
  return (($_SESSION['user']['role'] ?? '') === 'admin');
}
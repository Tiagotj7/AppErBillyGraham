<?php
declare(strict_types=1);

require_once __DIR__ . "/env.php";
require_once __DIR__ . "/security.php";

// 1) Carrega .env (tente fora do public_html primeiro)
$envPaths = [
  dirname(__DIR__, 2) . "/.env", // se /app estiver em /public_html/app, isso sobe 2 níveis
  __DIR__ . "/../.env",          // fallback: /public_html/.env
];

foreach ($envPaths as $p) {
  if (is_readable($p)) {
    env_load($p);
    break;
  }
}

security_headers();
start_secure_session();

$DB_HOST = env("DB_HOST");
$DB_NAME = env("DB_NAME");
$DB_USER = env("DB_USER");
$DB_PASS = env("DB_PASS");

if (!$DB_HOST || !$DB_NAME || !$DB_USER) {
  http_response_code(500);
  exit("Configuração do banco não encontrada (.env).");
}

try {
  $pdo = new PDO(
    "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
    $DB_USER,
    (string)$DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]
  );
} catch (Throwable $e) {
  http_response_code(500);
  exit("Erro ao conectar no banco.");
}
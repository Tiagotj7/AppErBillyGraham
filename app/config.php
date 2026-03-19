<?php
declare(strict_types=1);

require_once __DIR__ . "/env.php";
require_once __DIR__ . "/security.php";

// Carregar .env (InfinityFree: normalmente em /public_html/.env)
$envPaths = [
  __DIR__ . "/../.env",          // /public_html/.env  (pois /app/config.php -> ../.env)
  dirname(__DIR__) . "/.env",    // fallback redundante
];

$loaded = false;
foreach ($envPaths as $p) {
  if (is_readable($p)) {
    env_load($p);
    $loaded = true;
    break;
  }
}

security_headers();
start_secure_session();

$DB_HOST = env("DB_HOST");
$DB_NAME = env("DB_NAME");
$DB_USER = env("DB_USER");
$DB_PASS = env("DB_PASS");

if (!$loaded || !$DB_HOST || !$DB_NAME || !$DB_USER) {
  http_response_code(500);
  // Mensagem mais útil para diagnosticar
  exit("Configuração do banco não encontrada (.env). Verifique se existe /public_html/.env e se está legível.");
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
  exit("Erro ao conectar no banco. Confira DB_HOST/DB_NAME/DB_USER/DB_PASS no .env");
}
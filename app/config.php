<?php
declare(strict_types=1);

require_once __DIR__ . "/env.php";
require_once __DIR__ . "/security.php";

// Caminho absoluto do diretório público (onde está o index.php)
$publicRoot = realpath(__DIR__ . "/.."); // /public_html (se app está dentro dele)
$envFile = $publicRoot ? ($publicRoot . "/.env") : null;

if ($envFile && is_file($envFile) && is_readable($envFile)) {
  env_load($envFile);
}

security_headers();
start_secure_session();

$DB_HOST = env("DB_HOST");
$DB_NAME = env("DB_NAME");
$DB_USER = env("DB_USER");
$DB_PASS = env("DB_PASS");

if (!$DB_HOST || !$DB_NAME || !$DB_USER) {
  http_response_code(500);
  exit(
    "Configuração do banco não encontrada (.env). " .
    "Confirme que existe /public_html/.env e que DB_HOST/DB_NAME/DB_USER estão preenchidos."
  );
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
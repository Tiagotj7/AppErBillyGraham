<?php
declare(strict_types=1);

require_once __DIR__ . "/env.php";
require_once __DIR__ . "/security.php";

/**
 * Raiz do projeto = pasta que contém index.php e .env
 * Como config.php está em /app, a raiz é sempre o diretório pai.
 */
$projectRoot = realpath(__DIR__ . "/.."); // ex: /AppErBillyGraham
$envPaths = [];

// tenta carregar .env da raiz do projeto
if ($projectRoot) {
  $envPaths[] = $projectRoot . "/.env";
}

// fallback: .env no mesmo dir do config (não recomendado)
$envPaths[] = __DIR__ . "/.env";

$loadedFile = null;
foreach ($envPaths as $p) {
  if (is_file($p) && is_readable($p)) {
    env_load($p);
    $loadedFile = $p;
    break;
  }
}

security_headers();
start_secure_session();

$DB_HOST = env("DB_HOST");
$DB_NAME = env("DB_NAME");
$DB_USER = env("DB_USER");
$DB_PASS = env("DB_PASS");

if (!$loadedFile || !$DB_HOST || !$DB_NAME || !$DB_USER) {
  http_response_code(500);
  exit(
    "Configuração do banco não encontrada (.env). " .
    "Procurei em: " . implode(" | ", $envPaths) . ". " .
    "Confira se o arquivo existe e contém DB_HOST/DB_NAME/DB_USER."
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
  exit("Erro ao conectar no banco. Verifique DB_HOST/DB_NAME/DB_USER/DB_PASS no .env");
}
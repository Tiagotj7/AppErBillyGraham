<?php
declare(strict_types=1);

require_once __DIR__ . "/env.php";
require_once __DIR__ . "/security.php";

$root = realpath(__DIR__ . "/.."); // .../htdocs
$envFile = $root ? ($root . "/.env") : null;

$loadedFile = null;
if ($envFile && is_file($envFile) && is_readable($envFile)) {
  env_load($envFile);
  $loadedFile = $envFile;
}

security_headers();
start_secure_session();

$DB_HOST = env("DB_HOST");
$DB_NAME = env("DB_NAME");
$DB_USER = env("DB_USER");
$DB_PASS = env("DB_PASS");

if (!$loadedFile) {
  http_response_code(500);
  exit("Não foi possível localizar o .env em: " . ($envFile ?? '(null)'));
}

if (!$DB_HOST || !$DB_NAME || !$DB_USER) {
  http_response_code(500);
  exit(
    "Variáveis do banco vazias no .env. " .
    "Encontrado em: {$loadedFile}. " .
    "Confira DB_HOST/DB_NAME/DB_USER."
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
  exit("Erro ao conectar no banco. Confira as credenciais no .env.");
}
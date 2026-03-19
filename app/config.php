<?php
declare(strict_types=1);
session_start();

$DB_HOST = "sqlXXX.infinityfree.com";  // pegue no painel
$DB_NAME = "if0_XXXXXXX_dbname";
$DB_USER = "if0_XXXXXXX";
$DB_PASS = "SUA_SENHA";

try {
  $pdo = new PDO(
    "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (Throwable $e) {
  http_response_code(500);
  exit("Erro ao conectar no banco.");
}
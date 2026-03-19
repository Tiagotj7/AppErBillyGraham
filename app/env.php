<?php
declare(strict_types=1);

function env_load(string $path): void {
  if (!is_readable($path)) return;

  $raw = file_get_contents($path);
  if ($raw === false) return;

  // Remove BOM UTF-8 se existir
  $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

  $lines = preg_split("/\r\n|\n|\r/", $raw);
  if (!$lines) return;

  foreach ($lines as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#')) continue;

    // aceita "export KEY=VALUE"
    if (str_starts_with($line, 'export ')) {
      $line = trim(substr($line, 7));
    }

    $pos = strpos($line, '=');
    if ($pos === false) continue;

    $key = trim(substr($line, 0, $pos));
    $val = trim(substr($line, $pos + 1));

    // remove aspas
    if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
        (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
      $val = substr($val, 1, -1);
    }

    if ($key === '') continue;

    // Seta no ambiente
    putenv($key . '=' . $val);
    $_ENV[$key] = $val;
  }
}

function env(string $key, ?string $default = null): ?string {
  $v = getenv($key);
  if ($v === false || $v === '') return $default;
  return $v;
}
<?php
declare(strict_types=1);

function env_load(string $path): void {
  if (!is_readable($path)) return;

  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if (!$lines) return;

  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;

    $pos = strpos($line, '=');
    if ($pos === false) continue;

    $key = trim(substr($line, 0, $pos));
    $val = trim(substr($line, $pos + 1));

    // remove aspas
    if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
        (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
      $val = substr($val, 1, -1);
    }

    // não sobrescrever se já existir
    if (getenv($key) === false) {
      putenv($key . '=' . $val);
      $_ENV[$key] = $val;
    }
  }
}

function env(string $key, ?string $default = null): ?string {
  $v = getenv($key);
  if ($v === false) return $default;
  return $v;
}
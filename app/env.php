<?php
declare(strict_types=1);

$GLOBALS['__ENV_STORE'] = $GLOBALS['__ENV_STORE'] ?? [];

function env_load(string $path): void {
  if (!is_readable($path)) return;

  $raw = file_get_contents($path);
  if ($raw === false) return;

  $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw); // remove BOM

  $lines = preg_split("/\r\n|\n|\r/", $raw);
  if (!$lines) return;

  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;

    if (str_starts_with($line, 'export ')) {
      $line = trim(substr($line, 7));
    }

    $pos = strpos($line, '=');
    if ($pos === false) continue;

    $key = trim(substr($line, 0, $pos));
    $val = trim(substr($line, $pos + 1));
    if ($key === '') continue;

    if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
        (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
      $val = substr($val, 1, -1);
    }

    $_ENV[$key] = $val;
    $GLOBALS['__ENV_STORE'][$key] = $val;
  }
}

function env(string $key, ?string $default = null): ?string {
  if (isset($GLOBALS['__ENV_STORE'][$key]) && $GLOBALS['__ENV_STORE'][$key] !== '') {
    return (string)$GLOBALS['__ENV_STORE'][$key];
  }
  if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
    return (string)$_ENV[$key];
  }
  return $default;
}
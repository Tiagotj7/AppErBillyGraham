<?php
declare(strict_types=1);

function security_headers(): void {
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: SAMEORIGIN');
  header('Referrer-Policy: strict-origin-when-cross-origin');
}

function start_secure_session(): void {
  $name = env('SESSION_NAME', 'FREQSESS') ?? 'FREQSESS';
  $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

  session_name($name);
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);

  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
}

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function csrf_input(): string {
  return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify(): void {
  $sent = $_POST['csrf_token'] ?? '';
  $sess = $_SESSION['csrf_token'] ?? '';
  if (!$sent || !$sess || !hash_equals($sess, $sent)) {
    http_response_code(403);
    exit('CSRF inválido.');
  }
}

function session_regenerate_on_login(): void {
  session_regenerate_id(true);
}
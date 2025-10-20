<?php
// Lightweight DB + session utilities used across the app.

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function env_read($path = __DIR__ . '/.env') {
  $vars = [];
  if (!file_exists($path)) return $vars;
  foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $v = trim($v, "\"' ");
    $vars[$k] = $v;
  }
  return $vars;
}

function db_connect() {
  static $conn = null;
  if ($conn instanceof mysqli) return $conn;

  $env = env_read(__DIR__ . '/.env');
  $server = $env['server'] ?? 'localhost';
  $user   = $env['user'] ?? 'root';
  $pass   = $env['pass'] ?? '';
  $dbName = $env['dbName'] ?? 'disgustingPizza';

  $conn = @mysqli_connect($server, $user, $pass, $dbName);
  if (!$conn) {
    http_response_code(500);
    die('Database connection failed');
  }
  // Ensure utf8.
  mysqli_set_charset($conn, 'utf8mb4');
  return $conn;
}

function is_logged_in() {
  return isset($_SESSION['user']) && $_SESSION['user'] === 'Gio';
}

function require_login() {
  if (!is_logged_in()) {
    header('Location: login.php');
    exit;
  }
}
?>

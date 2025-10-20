<?php
require_once __DIR__ . '/../conn.php';

// Handle logout
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: login.php');
  exit;
}

function safe_equals($a, $b) {
  if (function_exists('hash_equals')) {
    return hash_equals($a, $b);
  }
  $a = (string)$a; $b = (string)$b;
  $len = strlen($a);
  if ($len !== strlen($b)) return false;
  $res = 0;
  for ($i = 0; $i < $len; $i++) {
    $res |= ord($a[$i]) ^ ord($b[$i]);
  }
  return $res === 0;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = isset($_POST['username']) ? trim($_POST['username']) : '';
  $password = isset($_POST['password']) ? $_POST['password'] : '';

  $env = env_read(__DIR__ . '/../.env');
  $expectedPass = isset($env['GIO_PASSWORD']) ? (string)$env['GIO_PASSWORD'] : 'gio123';

  if (strcasecmp($username, 'Gio') === 0 && safe_equals($expectedPass, $password)) {
    $_SESSION['user'] = 'Gio';
    header('Location: admin.php');
    exit;
  } else {
    $error = 'Fel användarnamn eller lösenord.';
  }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <style>
    body { font-family: system-ui, sans-serif; padding: 2rem; }
    form { max-width: 340px; display: grid; gap: .75rem; }
    label { font-weight: 600; }
    input { padding: .5rem; }
    .error { color: #b00020; }
  </style>
  </head>
<body>
  <h1>Logga in</h1>
  <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
  <form method="post" action="login.php">
    <label for="username">Användarnamn</label>
    <input type="text" id="username" name="username" required />

    <label for="password">Lösenord</label>
    <input type="password" id="password" name="password" required />

    <button type="submit">Logga in</button>
  </form>
</body>
</html>

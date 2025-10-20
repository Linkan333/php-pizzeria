<?php

$alreadyInstalled = file_exists(__DIR__ . '/../.env');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once __DIR__ . '/../installscripts.php';
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Installera</title>
  <style>
    body { font-family: system-ui, sans-serif; padding: 1.5rem; }
    header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
    header a { color:#2563eb; text-decoration:none; }
    header a:hover { text-decoration:underline; }
    .card { border: 1px solid #ddd; border-radius: 8px; background: #fff; max-width: 760px; }
    .section { padding: 1rem; }
    label { display:block; font-weight:600; margin:.5rem 0 .25rem; }
    input { width:100%; border:1px solid #ddd; border-radius:6px; padding:.5rem; }
    .grid { display:grid; gap:1rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
    .actions { margin-top:1rem; }
    button { border:1px solid #ccc; border-radius:6px; padding:.6rem 1rem; background:#f8f9fb; cursor:pointer; }
    .muted { color:#555; font-size:.9rem; }
    .note { background:#fffbe6; border:1px solid #ffe58f; padding:.5rem .75rem; border-radius:6px; }
  </style>
</head>
<body>
  <header>
    <h1>Installera</h1>
    <div><a href="index.php">Till meny</a></div>
  </header>

  <div class="card">
    <div class="section">
      <?php if ($alreadyInstalled): ?>
        <p class="note">.env finns redan. Om du kör installationen igen kan befintliga inställningar skrivas över.</p>
      <?php endif; ?>
      <form method="post">
        <h3>Databas</h3>
        <div class="grid">
          <div>
            <label for="host">Värd</label>
            <input type="text" id="host" name="host" placeholder="localhost" required />
          </div>
          <div>
            <label for="database">Databas</label>
            <input type="text" id="database" name="database" placeholder="disgustingPizza" required />
          </div>
          <div>
            <label for="user">Användare</label>
            <input type="text" id="user" name="user" placeholder="root" required />
          </div>
          <div>
            <label for="password">Lösenord</label>
            <input type="password" id="password" name="password" placeholder="" />
          </div>
        </div>

        <h3 style="margin-top:1rem;">Gio inlogg</h3>
        <div class="grid">
          <div>
            <label for="gio_user">Användarnamn</label>
            <input type="text" id="gio_user" name="gio_user" placeholder="Gio" required />
          </div>
          <div>
            <label for="gio_pass">Lösenord</label>
            <input type="password" id="gio_pass" name="gio_pass" placeholder="gio123" required />
          </div>
        </div>

        <div class="actions">
          <button type="submit">Installera</button>
        </div>
      </form>
      <p class="muted">Installationen skapar tabellerna <code>meny</code>, <code>info</code>, <code>users</code>, lägger till en testpizza och skriver .env.</p>
    </div>
  </div>
</body>
</html>


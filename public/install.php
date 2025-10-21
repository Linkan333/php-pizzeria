<?php

$alreadyInstalled = file_exists(__DIR__ . '/../.env');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once __DIR__ . '/../installscripts.php';
  exit;
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
        <p class="note">Installationen verkar redan vara konfigurerad (.env finns). Du kan köra om installationen för att återskapa databasen/tabellerna om något saknas.</p>
      <?php else: ?>
        <p class="muted">Klicka på Installera för att skapa databasen, tabellerna och standarddata. Inga inställningar behövs.</p>
      <?php endif; ?>

      <form method="post">
        <div class="actions">
          <button type="submit">Installera</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>

<?php
require_once __DIR__ . '/../conn.php';
$conn = db_connect();
$pizzas = [];
$res = $conn->query('SELECT * FROM meny ORDER BY pizzor');
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $pizzas[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Meny</title>
  <style>
    body { font-family: system-ui, sans-serif; padding: 1.5rem; }
    header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
    .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
    .card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; }
    .card img { width: 100%; height: 160px; object-fit: cover; background: #f7f7f7; }
    .card .info { padding: .75rem; }
    .name { font-weight: 700; margin-bottom: .25rem; }
    .ing { color: #444; font-size: .95rem; }
  </style>
  </head>
<body>
  <header>
    <h1>Vår meny</h1>
    <a href="admin.php">Admin</a>
  </header>

  <section class="grid">
  <?php foreach ($pizzas as $p):
    $name = isset($p['pizzor']) ? $p['pizzor'] : '';
    $img  = isset($p['image']) ? $p['image'] : '';
    $ing  = '';
    $price = isset($p['priser']) ? $p['priser'] : '';
    if (isset($p['Ingredienser'])) { $ing = $p['Ingredienser']; }
    elseif (isset($p['ingredienser'])) { $ing = $p['ingredienser']; }
    elseif (isset($p['ingredients'])) { $ing = $p['ingredients']; }
  ?>
    <article class="card">
      <?php if ($img): ?>
        <img src="uploads/<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($name); ?>" />
      <?php else: ?>
        <img src="data:image/svg+xml;charset=UTF-8,<?php echo rawurlencode('<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'800\' height=\'600\'><rect width=\'100%\' height=\'100%\' fill=\'#efefef\'/><text x=\'50%\' y=\'50%\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'#9a9a9a\' font-size=\'24\'>Ingen bild</text></svg>'); ?>" alt="Ingen bild" />
      <?php endif; ?>
      <div class="info">
        <div class="name"><?php echo htmlspecialchars($name); ?> - <?php echo htmlspecialchars($price); ?> KR</div>
        <?php if ($ing !== ''): ?>
          <div class="ing"><?php echo htmlspecialchars($ing); ?></div>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
  </section>
</body>
</html>

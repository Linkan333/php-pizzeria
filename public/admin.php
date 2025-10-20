<?php
require_once __DIR__ . '/../conn.php';
require_login();
$conn = db_connect();

$pizzas = [];
$result = $conn->query("SELECT pizzor FROM meny ORDER BY pizzor");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $pizzas[] = $row['pizzor'];
  }
} else {
  echo '<p style="color:#b00020">Kunde inte hämta pizzor: ' . htmlspecialchars($conn->error) . '</p>';
}

$selectedPizza = isset($_POST['pizzor']) ? $_POST['pizzor'] : (isset($_GET['pizzor']) ? $_GET['pizzor'] : '');
$pizza = null;
$pizzaImage = '';
if ($selectedPizza) {
  $name = $conn->real_escape_string($selectedPizza);
  $res = $conn->query("SELECT * FROM meny WHERE pizzor='" . $name . "' LIMIT 1");
  if ($res && ($row = $res->fetch_assoc())) {
    $pizza = $row;
    if (isset($row['image'])) {
      $pizzaImage = $row['image'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sida</title>
  <style>
    body { font-family: system-ui, sans-serif; padding: 1.5rem; }
    header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
    header a { color:#2563eb; text-decoration:none; }
    header a:hover { text-decoration:underline; }
    .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; }
    .card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; }
    .section { padding: .75rem; }
    .card img { width: 100%; height: 180px; object-fit: cover; background: #f7f7f7; }
    label { display:block; font-weight: 600; margin: .5rem 0 .25rem; }
    select, input[type=text], input[type=number], input[type=file] { width:100%; border:1px solid #ddd; border-radius:6px; padding:.5rem; }
    .actions { margin-top:.75rem; display:flex; gap:.5rem; }
    button { border:1px solid #ccc; border-radius:6px; padding:.5rem .75rem; background:#f8f9fb; cursor:pointer; }
    .danger { background:#fff0f0; border-color:#f3c2c2; color:#b00020; }
    .muted { color:#555; font-size:.9rem; }
  </style>
</head>
<body>
  <header>
    <h1>Admin</h1>
    <div>
      Inloggad som Gio · <a href="login.php?logout=1">Logga ut</a> · <a href="index.php">Till meny</a>
    </div>
  </header>

  <div class="card">
    <div class="section">
      <label for="pizzor">Välj pizza</label>
      <form method="post">
        <select name="pizzor" id="pizzor" onchange="this.form.submit()">
          <option value="">--Välj en pizza--</option>
          <?php foreach ($pizzas as $pName): ?>
            <option value="<?php echo htmlspecialchars($pName); ?>" <?php echo ($selectedPizza === $pName) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($pName); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <noscript><div class="actions"><button type="submit">Välj</button></div></noscript>
      </form>
      <?php if (!$selectedPizza && empty($pizzas)): ?>
        <p class="muted">Hittade inga pizzor i databasen. Kontrollera tabellen <code>meny</code> och kolumnen <code>pizzor</code>.</p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($pizza): ?>
    <?php
      $ing = '';
      if (isset($pizza['Ingredienser'])) { $ing = $pizza['Ingredienser']; }
      elseif (isset($pizza['ingredienser'])) { $ing = $pizza['ingredienser']; }
      elseif (isset($pizza['ingredients'])) { $ing = $pizza['ingredients']; }
      $pris = isset($pizza['priser']) ? (int)$pizza['priser'] : 0;
    ?>
    <div class="grid">
      <div class="card">
        <div class="section">
          <h3><?php echo htmlspecialchars($selectedPizza); ?></h3>
          <?php if ($pizzaImage): ?>
            <img src="uploads/<?php echo htmlspecialchars($pizzaImage); ?>" alt="Pizza bild">
          <?php else: ?>
            <div class="muted">Ingen bild uppladdad</div>
          <?php endif; ?>
          <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="pizza_name" value="<?php echo htmlspecialchars($selectedPizza); ?>">
            <label for="pizza_image">Ladda upp ny bild</label>
            <input type="file" name="pizza_image" id="pizza_image" accept="image/*" required>
            <div class="actions"><button type="submit">Ladda upp</button></div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="section">
          <h3>Redigera pizza</h3>
          <form action="pizza_save.php" method="post">
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="original_name" value="<?php echo htmlspecialchars($selectedPizza); ?>" />
            <label>Namnet</label>
            <input type="text" name="pizzor" value="<?php echo htmlspecialchars($selectedPizza); ?>" required />
            <label>Ingredienser</label>
            <input type="text" name="ingredients" value="<?php echo htmlspecialchars($ing); ?>" />
            <label>Pris (kr)</label>
            <input type="number" name="priser" value="<?php echo (int)$pris; ?>" min="0" step="1" />
            <div class="actions">
              <button type="submit">Spara</button>
              </div>
          </form>
          <form action="pizza_delete.php" method="post" onsubmit="return confirm('Ta bort denna pizza?');">
            <input type="hidden" name="pizza_name" value="<?php echo htmlspecialchars($selectedPizza); ?>" />
            <div class="actions"><button class="danger" type="submit">Radera pizza</button></div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="card" style="margin-top:1rem;">
    <div class="section">
      <h3>Lägg till ny pizza</h3>
      <form action="pizza_save.php" method="post">
        <input type="hidden" name="action" value="create" />
        <label>Namnet</label>
        <input type="text" name="pizzor" required />
        <label>Ingredienser</label>
        <input type="text" name="ingredients" />
        <label>Pris (kr)</label>
        <input type="number" name="priser" value="0" min="0" step="1" />
        <div class="actions"><button type="submit">Lägg till</button></div>
      </form>
    </div>
  </div>
</body>
</html>

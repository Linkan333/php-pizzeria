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
</head>
<body>
  <h1>Admin</h1>
  <p>Inloggad som Gio. <a href="login.php?logout=1">Logga ut</a></p>

  <form method="post">
    <label for="pizzor">Välj pizza:</label>
    <select name="pizzor" id="pizzor" onchange="this.form.submit()">
      <option value="">--Välj en pizza--</option>
      <?php foreach ($pizzas as $pName): ?>
        <option value="<?php echo htmlspecialchars($pName); ?>" <?php echo ($selectedPizza === $pName) ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($pName); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <noscript><button type="submit">Välj</button></noscript>
  </form>

  <?php if ($pizza): ?>
    <h2><?php echo htmlspecialchars($selectedPizza); ?></h2>
    <?php if ($pizzaImage): ?>
      <img src="uploads/<?php echo htmlspecialchars($pizzaImage); ?>" alt="Pizza bild" style="max-width:300px;"><br>
    <?php else: ?>
      <p>Ingen bild uppladdad.</p>
    <?php endif; ?>

    <?php
      $ing = '';
      if (isset($pizza['Ingredienser'])) { $ing = $pizza['Ingredienser']; }
      elseif (isset($pizza['ingredienser'])) { $ing = $pizza['ingredienser']; }
      elseif (isset($pizza['ingredients'])) { $ing = $pizza['ingredients']; }
      $pris = isset($pizza['priser']) ? (int)$pizza['priser'] : 0;
    ?>

    <h3>Redigera pizza</h3>
    <form action="pizza_save.php" method="post">
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="original_name" value="<?php echo htmlspecialchars($selectedPizza); ?>" />
      <div>
        <label>Namnet</label><br>
        <input type="text" name="pizzor" value="<?php echo htmlspecialchars($selectedPizza); ?>" required />
      </div>
      <div>
        <label>Ingredienser</label><br>
        <input type="text" name="ingredients" value="<?php echo htmlspecialchars($ing); ?>" />
      </div>
      <div>
        <label>Pris (kr)</label><br>
        <input type="number" name="priser" value="<?php echo (int)$pris; ?>" min="0" step="1" />
      </div>
      <button type="submit">Spara</button>
    </form>

    <form action="upload.php" method="post" enctype="multipart/form-data" style="margin-top:1rem;">
      <input type="hidden" name="pizza_name" value="<?php echo htmlspecialchars($selectedPizza); ?>">
      <label for="pizza_image">Ladda upp ny bild:</label>
      <input type="file" name="pizza_image" id="pizza_image" accept="image/*" required>
      <button type="submit">Ladda Upp</button>
    </form>

    <form action="pizza_delete.php" method="post" onsubmit="return confirm('Ta bort denna pizza?');" style="margin-top:1rem;">
      <input type="hidden" name="pizza_name" value="<?php echo htmlspecialchars($selectedPizza); ?>" />
      <button type="submit" style="color:#fff;background:#b00020;">Radera pizza</button>
    </form>
  <?php endif; ?>

  <?php if (!$selectedPizza && empty($pizzas)): ?>
    <p>Hittade inga pizzor i databasen. Kontrollera tabellen <code>meny</code> och kolumnen <code>pizzor</code>.</p>
  <?php endif; ?>

  <hr>
  <h3>Lägg till ny pizza</h3>
  <form action="pizza_save.php" method="post">
    <input type="hidden" name="action" value="create" />
    <div>
      <label>Namnet</label><br>
      <input type="text" name="pizzor" required />
    </div>
    <div>
      <label>Ingredienser</label><br>
      <input type="text" name="ingredients" />
    </div>
    <div>
      <label>Pris (kr)</label><br>
      <input type="number" name="priser" value="0" min="0" step="1" />
    </div>
    <button type="submit">Lägg till</button>
  </form>

  <p><a href="index.php">Gå Tillbaka</a></p>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sida</title>
</head>
<body>
  <h1>ADMIN (DANGER)</h1>
  <?php
  $server = "localhost";
  $user = "root";
  $pass = "";
  $dbname = "disgustingPizza";
  $conn = mysqli_connect($server, $user, $pass, $dbname);
  if (!$conn) {
    echo "Connection failed";
    exit;
  }

  $sql = "SELECT pizzor FROM meny ORDER BY pizzor";
  $result = $conn->query($sql);
  $pizzas = [];
  while ($row = $result->fetch_assoc()) {
    $pizzas[] = $row['pizzor'];
  }

  $selectedPizza = $_POST['pizzor'] ?? '';

  $pizzaImage = '';
  if ($selectedPizza) {
    $stmt = $conn->prepare("SELECT image FROM meny WHERE pizzor=?");
    $stmt->bind_param("s", $selectedPizza);
    $stmt->execute();
    $stmt->bind_result($pizzaImage);
    $stmt->fetch();
    $stmt->close();
  }
  ?>

  <form method="post" enctype="multipart/form-data">
    <label for="pizzor">Välj pizza:</label>
    <select name="pizzor" id="pizzor" onchange="this.form.submit()">
      <option value="">--Välj en pizza--</option>
      <?php foreach ($pizzas as $pizza): ?>
        <option value="<?= htmlspecialchars($pizza) ?>" <?= $selectedPizza === $pizza ? 'selected' : '' ?>>
          <?= htmlspecialchars($pizza) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <noscript><button type="submit">Välj</button></noscript>
  </form>

  <?php if ($selectedPizza): ?>
    <h2><?= htmlspecialchars($selectedPizza) ?></h2>
    <?php if ($pizzaImage): ?>
      <img src="../uploads/<?= htmlspecialchars($pizzaImage) ?>" alt="Pizza bild" style="max-width:300px;"><br>
    <?php else: ?>
      <p>Ingen bild uppladdad.</p>
    <?php endif; ?>

    <form action="../upload.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="pizza_name" value="<?= htmlspecialchars($selectedPizza) ?>">
      <label for="pizza_image">Ladda upp ny bild:</label>
      <input type="file" name="pizza_image" id="pizza_image" accept="image/*" required>
      <button type="submit">Ladda Upp</button>
    </form>
  <?php endif; ?>

  <p><a href="index.php">Gå Tillbaka</a></p>
</body>
</html>
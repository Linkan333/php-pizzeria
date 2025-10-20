<?php
require_once __DIR__ . '/../conn.php';
require_login();
$conn = db_connect();

function find_ingredients_col($conn) {
  $candidates = array('Ingredienser','ingredienser','ingredients');
  $res = $conn->query('SHOW COLUMNS FROM meny');
  if ($res) {
    while ($row = $res->fetch_assoc()) {
      $col = $row['Field'];
      foreach ($candidates as $cand) {
        if (strcasecmp($col, $cand) === 0) return $col;
      }
    }
  }
  return 'Ingredienser';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Method Not Allowed';
  exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$name   = isset($_POST['pizzor']) ? trim($_POST['pizzor']) : '';
$ing    = isset($_POST['ingredients']) ? trim($_POST['ingredients']) : '';
$price  = isset($_POST['priser']) ? (int)$_POST['priser'] : 0;
$colIng = find_ingredients_col($conn);

if ($action === 'update') {
  $orig  = isset($_POST['original_name']) ? trim($_POST['original_name']) : '';
  if ($orig === '' || $name === '') {
    http_response_code(400);
    echo 'Saknar data';
    exit;
  }
  $sql = "UPDATE meny SET pizzor=?, `" . $colIng . "`=?, priser=? WHERE pizzor=?";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    http_response_code(500);
    echo 'Fel: ' . htmlspecialchars($conn->error);
    exit;
  }
  $stmt->bind_param('ssis', $name, $ing, $price, $orig);
  $stmt->execute();
  $stmt->close();
  header('Location: admin.php?pizzor=' . urlencode($name));
  exit;
}

if ($action === 'create') {
  if ($name === '') {
    http_response_code(400);
    echo 'Ange namn';
    exit;
  }
  $sql = "INSERT INTO meny (pizzor, `" . $colIng . "`, priser) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    http_response_code(500);
    echo 'Fel: ' . htmlspecialchars($conn->error);
    exit;
  }
  $stmt->bind_param('ssi', $name, $ing, $price);
  $stmt->execute();
  $stmt->close();
  header('Location: admin.php?pizzor=' . urlencode($name));
  exit;
}

http_response_code(400);
echo 'Okänt kommando';
?>


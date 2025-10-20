<?php
require_once __DIR__ . '/../conn.php';
require_login();
$conn = db_connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Method Not Allowed';
  exit;
}

$name = isset($_POST['pizza_name']) ? trim($_POST['pizza_name']) : '';
if ($name === '') {
  http_response_code(400);
  echo 'Saknar namn';
  exit;
}

$stmt = $conn->prepare('DELETE FROM meny WHERE pizzor = ?');
if (!$stmt) {
  http_response_code(500);
  echo 'Fel: ' . htmlspecialchars($conn->error);
  exit;
}
$stmt->bind_param('s', $name);
$stmt->execute();
$stmt->close();

header('Location: admin.php');
exit;
?>


<?php
require_once __DIR__ . '/../conn.php';
if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Method Not Allowed';
  exit;
}

$pizzaName = isset($_POST['pizza_name']) ? $_POST['pizza_name'] : '';
if ($pizzaName === '' || !isset($_FILES['pizza_image'])) {
  http_response_code(400);
  echo 'Saknar data för uppladdning.';
  exit;
}

$file = $_FILES['pizza_image'];
if ($file['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo 'Fel vid uppladdning: ' . (int)$file['error'];
  exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);
$allowed = [
  'image/jpeg' => 'jpg',
  'image/png'  => 'png',
  'image/gif'  => 'gif',
  'image/webp' => 'webp'
];
if (!isset($allowed[$mime])) {
  http_response_code(400);
  echo 'Endast bilder (jpg, png, gif, webp) är tillåtna.';
  exit;
}

$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
  if (!mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
    http_response_code(500);
    echo 'Kunde inte skapa uppladdningsmapp.';
    exit;
  }
}

$ext = $allowed[$mime];
$base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower(pathinfo($file['name'], PATHINFO_FILENAME)));
if ($base === '' || $base === '-') $base = 'pizza';
$targetName = $base . '-' . substr(sha1(uniqid('', true)), 0, 8) . '.' . $ext;
$targetPath = $uploadDir . '/' . $targetName;

if (!is_writable($uploadDir)) {
  @chmod($uploadDir, 0775);
}

$tmp = $file['tmp_name'];
if (!move_uploaded_file($tmp, $targetPath)) {
  $details = [];
  $details[] = 'Uppladdningsmapp: ' . $uploadDir;
  $details[] = 'Skrivbar mapp: ' . (is_writable($uploadDir) ? 'ja' : 'nej');
  $details[] = 'Tempfil finns: ' . (file_exists($tmp) ? 'ja' : 'nej');
  $details[] = 'Är uppladdad fil: ' . (function_exists('is_uploaded_file') && is_uploaded_file($tmp) ? 'ja' : 'nej');
  $details[] = 'upload_tmp_dir: ' . (ini_get('upload_tmp_dir') ?: '(standard)');
  $details[] = 'post_max_size: ' . ini_get('post_max_size');
  $details[] = 'upload_max_filesize: ' . ini_get('upload_max_filesize');

  $renameOk = false;
  if (file_exists($tmp)) {
    $renameOk = @rename($tmp, $targetPath);
  }
  if ($renameOk) {
  } else {
    http_response_code(500);
    echo 'Kunde inte spara filen.' . "\n" . implode("\n", $details);
    exit;
  }
}

$conn = db_connect();
$colRes = $conn->query("SHOW COLUMNS FROM meny LIKE 'image'");
if ($colRes && $colRes->num_rows === 0) {
  $conn->query("ALTER TABLE meny ADD COLUMN image VARCHAR(255) NULL");
}

$stmt = $conn->prepare('UPDATE meny SET image = ? WHERE pizzor = ?');
if ($stmt) {
  $stmt->bind_param('ss', $targetName, $pizzaName);
  $stmt->execute();
  $stmt->close();
}

header('Location: admin.php');
exit;
?>

<?php

// NOTE! AI GJORDE DETTA FÖR DET BLEV SVIN FUCKED UP
function respond($ok, $title, $messages) {
  if (!is_array($messages)) $messages = array($messages);
  echo "<!DOCTYPE html><html lang=\"sv\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"><title>Installera</title><style>
  body{font-family:system-ui,sans-serif;padding:1.5rem}
  .card{border:1px solid #ddd;border-radius:8px;background:#fff;max-width:760px}
  .section{padding:1rem}
  .ok{color:#065f46;background:#ecfdf5;border:1px solid #a7f3d0;padding:.5rem .75rem;border-radius:6px}
  .err{color:#991b1b;background:#fef2f2;border:1px solid #fecaca;padding:.5rem .75rem;border-radius:6px}
  a{color:#2563eb;text-decoration:none}
  a:hover{text-decoration:underline}
  ul{margin:.5rem 0 0 1.25rem}
  </style></head><body>";
  echo "<h1>Installera</h1>";
  echo "<div class=\"card\"><div class=\"section\">";
  echo $ok ? "<div class=ok>" . htmlspecialchars($title) . "</div>" : "<div class=err>" . htmlspecialchars($title) . "</div>";
  echo "<ul>";
  foreach ($messages as $m) { echo "<li>" . htmlspecialchars($m) . "</li>"; }
  echo "</ul>";
  echo "<p><a href=\"install.php\">Tillbaka</a> · <a href=\"login.php\">Logga in</a> · <a href=\"index.php\">Meny</a></p>";
  echo "</div></div></body></html>";
}
// AI GJORDE INTE DETTA DET VAR JAG

$host = isset($_POST['host']) ? trim($_POST['host']) : '';
$db   = isset($_POST['database']) ? trim($_POST['database']) : '';
$user = isset($_POST['user']) ? trim($_POST['user']) : '';
$pass = isset($_POST['password']) ? (string)$_POST['password'] : '';
$gioU = isset($_POST['gio_user']) ? trim($_POST['gio_user']) : 'Gio';
$gioP = isset($_POST['gio_pass']) ? (string)$_POST['gio_pass'] : 'gio123';

$errors = array();
if ($host === '' || $db === '' || $user === '') {
  $errors[] = 'Ange värd, databas och användare.';
}
if ($gioU === '' || $gioP === '') {
  $errors[] = 'Ange Gio användarnamn och lösenord.';
}
if (!empty($errors)) {
  respond(false, 'Fel i formuläret', $errors);
  return;
}

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
  $conn2 = @mysqli_connect($host, $user, $pass);
  if (!$conn2) {
    respond(false, 'Kunde inte ansluta', array(mysqli_connect_error()));
    return;
  }
  @mysqli_query($conn2, "CREATE DATABASE IF NOT EXISTS `" . $db . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
  @mysqli_close($conn2);
  $conn = @mysqli_connect($host, $user, $pass, $db);
  if (!$conn) {
    respond(false, 'Kunde inte skapa/ansluta till databasen', array(mysqli_connect_error()));
    return;
  }
}
mysqli_set_charset($conn, 'utf8mb4');

$messages = array();

$sqlMeny = "CREATE TABLE IF NOT EXISTS `meny` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pizzor` TEXT NOT NULL,
  `Ingredienser` VARCHAR(500) NULL,
  `priser` INT(11) NOT NULL DEFAULT 0,
  `image` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
if (!mysqli_query($conn, $sqlMeny)) { $errors[] = 'Skapa meny: ' . mysqli_error($conn); }
else { $messages[] = 'Tabell meny klar.'; }

$hasMeny = mysqli_query($conn, "SELECT COUNT(*) AS c FROM meny");
if ($hasMeny && ($row = mysqli_fetch_assoc($hasMeny)) && (int)$row['c'] === 0) {
  @mysqli_query($conn, "INSERT INTO meny (pizzor, Ingredienser, priser) VALUES ('Calzone', 'Skinka, ost, tomatsås', 110)");
  $messages[] = 'La till testpizza.';
}

$sqlInfo = "CREATE TABLE IF NOT EXISTS `info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(50) NOT NULL,
  `info` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
if (!mysqli_query($conn, $sqlInfo)) { $errors[] = 'Skapa info: ' . mysqli_error($conn); }
else { $messages[] = 'Tabell info klar.'; }

$defaults = array(array('Image',''), array('Öppetider',''), array('Kontakt',''));
foreach ($defaults as $row) {
  $type = mysqli_real_escape_string($conn, $row[0]);
  $info = mysqli_real_escape_string($conn, $row[1]);
  @mysqli_query($conn, "INSERT IGNORE INTO info (type, info) VALUES ('$type', '$info')");
}

$sqlUsers = "CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'super',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
if (!mysqli_query($conn, $sqlUsers)) { $errors[] = 'Skapa users: ' . mysqli_error($conn); }
else { $messages[] = 'Tabell users klar.'; }

$hash = function_exists('password_hash') ? password_hash($gioP, PASSWORD_DEFAULT) : hash('sha256', $gioP);
$u = mysqli_real_escape_string($conn, $gioU);
$h = mysqli_real_escape_string($conn, $hash);
@mysqli_query($conn, "INSERT INTO users (username, password_hash, role) VALUES ('$u', '$h', 'super') ON DUPLICATE KEY UPDATE password_hash='$h', role='super'");
$messages[] = 'Skapade/updaterade Gio-användare.';
$env = array(
  'server' => $host,
  'user' => $user,
  'pass' => $pass,
  'dbName' => $db,
  'GIO_USERNAME' => $gioU,
  'GIO_PASSWORD' => $gioP,
  'DB_HOST' => $host,
  'DB_PORT' => '3306',
  'DB_DATABASE' => $db,
  'DB_USER' => $user,
  'DB_PASSWORD' => $pass,
);

$content = '';
foreach ($env as $k => $v) { $content .= $k . '=' . $v . "\n"; }
$file = __DIR__ . '/.env';
if (@file_put_contents($file, $content) !== false) {
  $messages[] = '.env skapad: ' . $file;
} else {
  $errors[] = 'Kunde inte skriva .env';
}

if (!empty($errors)) {
  respond(false, 'Installationen slutfördes med fel', array_merge($messages, $errors));
} else {
  respond(true, 'Installationen slutförd', $messages);
}

?>

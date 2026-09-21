<?php
// GN.01.db.php - Automatic Environment Detection Database Connection (MySQLi)

// Sunucu adresine bakarak ortamı otomatik tespit et
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    // 1. Local Environment Settings (XAMPP)
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'bimerp';
} else {
    // 2. Live Server Environment Settings (InfinityFree)
    $host = 'sql203.infinityfree.com';
    $user = 'if0_42942273';
    $pass = 'Da11sar11';
    $dbname = 'if0_42942273_bimerp';
}

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database Connection Error: " . $conn->connect_error);
}

// Türkçe karakterler için turkish_ci collate ayarı
$conn->set_charset("utf8mb4");
$conn->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_turkish_ci'");
?>
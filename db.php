<?php
$host = 'sql203.infinityfree.com';
$user = 'if0_42942273';
$pass = 'Da11sar11'; // InfinityFree panelindeki şifreniz
$dbname = 'if0_42942273_bimerp';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Veritabanı bağlantı hatası: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>

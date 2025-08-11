<?php
// db.php
$host = "localhost";
$port = "5432";
$dbname = "db_engine";
$user = "postgres";
$password = "postgres";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

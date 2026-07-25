<?php
$host = 'localhost';
$dbname = 'klinik'; // Sesuaikan dengan nama database Anda
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Konfigurasi Tarif
define('TARIF_KONSULTASI', 50000); // Rp 50.000 flat fee untuk konsultasi dokter
?>

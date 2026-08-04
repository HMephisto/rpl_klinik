<?php
// search_pasien.php — AJAX endpoint for searching existing patients
require_once 'includes/db.php';

// Session check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Resepsionis') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id_pasien, nama_pasien, tempat_tanggal_lahir, jenis_kelamin, alamat_pasien
    FROM Pasien
    WHERE nama_pasien LIKE ?
    ORDER BY nama_pasien ASC
    LIMIT 10
");
$stmt->execute(['%' . $q . '%']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);

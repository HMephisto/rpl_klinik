<?php
// run_migration.php
require_once __DIR__ . '/includes/db.php';

try {
    $pdo->exec("ALTER TABLE Obat ADD COLUMN harga DECIMAL(15,2) DEFAULT 0");
    echo "Added harga column to Obat.\n";
} catch (Exception $e) {
    echo "Column harga might already exist: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE Antrian MODIFY COLUMN status ENUM('Menunggu', 'Diperiksa', 'Menunggu Obat', 'Menunggu Pembayaran', 'Selesai', 'Batal') DEFAULT 'Menunggu'");
    echo "Modified status enum in Antrian.\n";
} catch (Exception $e) {
    echo "Failed to modify Antrian: " . $e->getMessage() . "\n";
}

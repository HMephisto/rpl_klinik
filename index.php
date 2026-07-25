<?php
// index.php
require_once 'includes/db.php';
require_once 'includes/header.php'; // header.php also starts session & checks login

$role = $_SESSION['role'] ?? '';
$tab = $_GET['tab'] ?? 'dashboard';

if ($role === 'Resepsionis') {
    if ($tab === 'dashboard' || $tab === 'pendaftaran') {
        // Dashboard is removed, default to Pendaftaran
        $activeTab = 'pendaftaran'; // Fallback for the active tab highlighting just in case
        include 'views/resepsionis/pendaftaran.php';
    } elseif ($tab === 'edit_antrian') {
        include 'views/resepsionis/edit_antrian.php';
    } elseif ($tab === 'kasir') {
        include 'views/resepsionis/kasir.php';
    } elseif ($tab === 'riwayat_pembayaran') {
        include 'views/resepsionis/riwayat_pembayaran.php';
    } elseif ($tab === 'detail_pembayaran') {
        include 'views/resepsionis/detail_pembayaran.php';
    } else {
        echo "<p>Page not found</p>";
    }
} elseif ($role === 'Dokter') {
    if ($tab === 'dashboard' || $tab === 'antrian') {
        $activeTab = 'antrian';
        include 'views/dokter/antrian.php';
    } elseif ($tab === 'emr') {
        include 'views/dokter/emr.php';
    } else {
        echo "<p>Page not found</p>";
    }
} elseif ($role === 'Apoteker') {
    if ($tab === 'dashboard' || $tab === 'antrian_resep') {
        $activeTab = 'antrian_resep';
        include 'views/apoteker/antrian_resep.php';
    } elseif ($tab === 'obat') {
        include 'views/apoteker/obat.php';
    } else {
        echo "<p>Page not found</p>";
    }
} else {
    echo '<div class="h-64 flex flex-col items-center justify-center text-slate-400 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50">';
    echo '<svg class="w-12 h-12 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>';
    echo '<p class="font-medium">Modul untuk peran ' . htmlspecialchars($role) . ' sedang dalam pengembangan.</p>';
    echo '</div>';
}

require_once 'includes/footer.php';
?>

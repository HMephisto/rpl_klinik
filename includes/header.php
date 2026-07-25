<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'Pasien';
$nama = $_SESSION['nama_lengkap'] ?? 'User';
$inisial = strtoupper(substr($nama, 0, 1));

// Determine active tab (simple routing simulation)
$activeTab = $_GET['tab'] ?? 'dashboard';

// Menu Items based on role
$menuItems = [];
if ($role === 'Resepsionis') {
    $menuItems = [
        ['id' => 'pendaftaran', 'label' => 'Pendaftaran & Antrian', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>', 'url' => 'index.php?tab=pendaftaran'],
        ['id' => 'kasir', 'label' => 'Kasir & Pembayaran', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>', 'url' => 'index.php?tab=kasir'],
        ['id' => 'riwayat_pembayaran', 'label' => 'Riwayat Pembayaran', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>', 'url' => 'index.php?tab=riwayat_pembayaran'],
    ];
} elseif ($role === 'Dokter') {
    $menuItems = [
        ['id' => 'antrian', 'label' => 'Antrian Pasien', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>', 'url' => 'index.php?tab=antrian'],
        ['id' => 'emr', 'label' => 'Rekam Medis (EMR)', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>', 'url' => 'index.php?tab=emr'],
    ];
} elseif ($role === 'Apoteker') {
    $menuItems = [
        ['id' => 'antrian_resep', 'label' => 'Antrian Resep', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>', 'url' => 'index.php?tab=antrian_resep'],
        ['id' => 'obat', 'label' => 'Manajemen Obat', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>', 'url' => 'index.php?tab=obat'],
    ];
}
// Other roles can be added here later
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Sehat</title>
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased">
    <div class="flex min-h-screen bg-slate-50/50">
        
        <!-- Sidebar -->
        <div class="w-64 bg-slate-900 text-white flex flex-col min-h-screen transition-all duration-300 shadow-xl z-20 shrink-0">
            <div class="p-6 flex items-center gap-3 border-b border-slate-700/50">
                <div class="bg-blue-500 p-2 rounded-lg text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight">Klinik Sehat</h1>
                    <p class="text-xs text-slate-400">Management System</p>
                </div>
            </div>
            
            <div class="flex-1 py-6 px-4 space-y-2">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4 px-2">Menu Utama</div>
                <?php foreach ($menuItems as $item): ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>" 
                       class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= $activeTab === $item['id'] ? 'bg-blue-600 text-white shadow-md shadow-blue-900/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
                        <?= $item['icon'] ?>
                        <span class="font-medium text-sm"><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="p-4 border-t border-slate-700/50">
                <a href="logout.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-red-400 hover:bg-red-500/10 hover:text-red-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span class="font-medium text-sm">Keluar</span>
                </a>
            </div>
        </div>

        <!-- Main Content Wrapper -->
        <main class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-10 shadow-sm">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-bold text-slate-800 capitalize">
                        <?= htmlspecialchars(str_replace('-', ' ', $activeTab)) ?>
                    </h2>
                </div>
                
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold border border-blue-200">
                            <?= htmlspecialchars($inisial) ?>
                        </div>
                        <div class="hidden md:block text-right">
                            <p class="text-sm font-bold text-slate-700 leading-tight"><?= htmlspecialchars($nama) ?></p>
                            <p class="text-xs text-slate-500"><?= htmlspecialchars($role) ?></p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content Area -->
            <div class="flex-1 p-8 overflow-y-auto">

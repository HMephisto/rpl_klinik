<?php
// views/resepsionis/detail_pembayaran.php
require_once 'includes/db.php';

$id_struk = $_GET['id'] ?? null;

if (!$id_struk) {
    echo '<div class="p-4 bg-red-50 text-red-600 rounded-lg">ID Struk tidak valid.</div>';
    return;
}

// Fetch receipt details
$query = "SELECT s.*, p.nama_pasien, p.alamat_pasien, TIMESTAMPDIFF(YEAR, p.tempat_tanggal_lahir, CURDATE()) as umur, p.jenis_kelamin, u.nama_lengkap as nama_staff 
          FROM Struk s 
          JOIN Pasien p ON s.id_pasien = p.id_pasien 
          JOIN Pengguna u ON s.id_staff = u.id_user 
          WHERE s.id_struk = :id_struk";
$stmt = $pdo->prepare($query);
$stmt->execute(['id_struk' => $id_struk]);
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$receipt) {
    echo '<div class="p-4 bg-red-50 text-red-600 rounded-lg">Struk tidak ditemukan.</div>';
    return;
}
?>

<div class="max-w-3xl mx-auto">
    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6 no-print">
        <a href="index.php?tab=riwayat_pembayaran" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-blue-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Struk
        </button>
    </div>

    <!-- Receipt Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden print-container">
        <!-- Clinic Header -->
        <div class="p-8 border-b border-slate-200 bg-slate-50 flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 mb-1">KLINIK SEHAT</h1>
                <p class="text-sm text-slate-500">Jl. Kesehatan No. 123, Kota Medika</p>
                <p class="text-sm text-slate-500">Telp: (021) 555-1234</p>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-bold text-slate-300 uppercase tracking-widest">INVOICE</h2>
                <p class="text-slate-800 font-medium mt-1">#STR-<?= str_pad($receipt['id_struk'], 5, '0', STR_PAD_LEFT) ?></p>
            </div>
        </div>

        <!-- Receipt Info -->
        <div class="p-8 border-b border-slate-200 grid grid-cols-2 gap-8">
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Informasi Pasien</h3>
                <div class="space-y-1">
                    <p class="font-bold text-slate-800"><?= htmlspecialchars($receipt['nama_pasien']) ?></p>
                    <p class="text-sm text-slate-600">Umur: <?= htmlspecialchars($receipt['umur'] ?? '-') ?> Tahun (<?= $receipt['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>)</p>
                    <p class="text-sm text-slate-600"><?= htmlspecialchars($receipt['alamat_pasien']) ?></p>
                </div>
            </div>
            <div class="text-right">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Detail Transaksi</h3>
                <div class="space-y-1">
                    <p class="text-sm text-slate-600"><span class="font-medium text-slate-700">Tanggal:</span> <?= date('d F Y', strtotime($receipt['tgl_bayar'])) ?></p>
                    <p class="text-sm text-slate-600"><span class="font-medium text-slate-700">Waktu:</span> <?= date('H:i', strtotime($receipt['tgl_bayar'])) ?></p>
                    <p class="text-sm text-slate-600"><span class="font-medium text-slate-700">Kasir:</span> <?= htmlspecialchars($receipt['nama_staff']) ?></p>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="p-8">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Rincian Layanan & Obat</h3>
            
            <?php 
                $rincian_json = json_decode($receipt['pemeriksaan_penunjang'], true);
                $is_json = (json_last_error() === JSON_ERROR_NONE && is_array($rincian_json));
            ?>
            
            <?php if ($is_json): ?>
                <table class="w-full text-left mb-6">
                    <thead class="border-b border-slate-200">
                        <tr>
                            <th class="py-3 text-sm font-semibold text-slate-700">Deskripsi</th>
                            <th class="py-3 text-sm font-semibold text-slate-700 text-center">Qty</th>
                            <th class="py-3 text-sm font-semibold text-slate-700 text-right">Harga</th>
                            <th class="py-3 text-sm font-semibold text-slate-700 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($rincian_json as $item): ?>
                        <tr>
                            <td class="py-3 text-sm text-slate-800"><?= htmlspecialchars($item['nama']) ?></td>
                            <td class="py-3 text-sm text-slate-600 text-center"><?= htmlspecialchars($item['qty']) ?></td>
                            <td class="py-3 text-sm text-slate-600 text-right">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td class="py-3 text-sm font-medium text-slate-700 text-right">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="bg-slate-50 rounded-lg p-4 mb-6 border border-slate-100">
                    <p class="text-sm text-slate-700 whitespace-pre-line"><?= htmlspecialchars($receipt['pemeriksaan_penunjang'] ?: 'Biaya Pemeriksaan & Layanan Klinik') ?></p>
                </div>
            <?php endif; ?>

            <!-- Total -->
            <div class="flex justify-end pt-4 border-t border-slate-200">
                <div class="w-64">
                    <div class="flex justify-between items-center text-lg">
                        <span class="font-bold text-slate-700">Total Tagihan</span>
                        <span class="font-bold text-blue-600">Rp <?= number_format($receipt['total_harga'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-50 p-6 text-center border-t border-slate-200">
            <p class="text-slate-500 text-sm italic">Terima kasih atas kunjungan Anda.<br>Semoga lekas sembuh.</p>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .print-container, .print-container * {
        visibility: visible;
    }
    .print-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none;
        border: none;
    }
    .no-print {
        display: none !important;
    }
}
</style>

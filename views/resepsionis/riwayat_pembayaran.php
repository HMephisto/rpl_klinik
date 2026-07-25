<?php
// views/resepsionis/riwayat_pembayaran.php
require_once 'includes/db.php';

// Fetch receipt history
$query = "SELECT s.id_struk, s.total_harga, s.tgl_bayar, p.nama_pasien, u.nama_lengkap as nama_staff 
          FROM Struk s 
          JOIN Pasien p ON s.id_pasien = p.id_pasien 
          JOIN Pengguna u ON s.id_staff = u.id_user 
          ORDER BY s.tgl_bayar DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50">
        <h3 class="text-lg font-bold text-slate-800">Riwayat Pembayaran</h3>
        <div class="relative">
            <input type="text" placeholder="Cari struk..." class="pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100 text-slate-500 text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 font-medium border-b border-slate-200">ID Struk</th>
                    <th class="px-6 py-4 font-medium border-b border-slate-200">Tanggal Bayar</th>
                    <th class="px-6 py-4 font-medium border-b border-slate-200">Nama Pasien</th>
                    <th class="px-6 py-4 font-medium border-b border-slate-200">Total Pembayaran</th>
                    <th class="px-6 py-4 font-medium border-b border-slate-200">Kasir/Staff</th>
                    <th class="px-6 py-4 font-medium border-b border-slate-200 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <?php if (count($receipts) > 0): ?>
                    <?php foreach ($receipts as $receipt): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-700">#STR-<?= str_pad($receipt['id_struk'], 5, '0', STR_PAD_LEFT) ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?= date('d M Y H:i', strtotime($receipt['tgl_bayar'])) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800"><?= htmlspecialchars($receipt['nama_pasien']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-emerald-600">Rp <?= number_format($receipt['total_harga'], 0, ',', '.') ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?= htmlspecialchars($receipt['nama_staff']) ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="index.php?tab=detail_pembayaran&id=<?= $receipt['id_struk'] ?>" class="text-blue-600 hover:text-blue-800 font-medium text-sm inline-flex items-center gap-1 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Belum ada riwayat pembayaran.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

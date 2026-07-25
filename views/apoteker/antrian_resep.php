<?php
// views/apoteker/antrian_resep.php

$msg = '';

// Handle Status Update Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'selesaikan') {
    $id_resep = $_POST['id_resep'] ?? '';
    if ($id_resep) {
        $stmtUpdate = $pdo->prepare("UPDATE Resep SET status = 'Selesai' WHERE id_resep = ?");
        if ($stmtUpdate->execute([$id_resep])) {
            // Update the Antrian status as well
            $stmtFindAntrian = $pdo->prepare("
                SELECT rm.id_antrian 
                FROM Resep r 
                JOIN Rekam_Medis rm ON r.nomor_RM = rm.nomor_RM 
                WHERE r.id_resep = ?
            ");
            $stmtFindAntrian->execute([$id_resep]);
            $rm_data = $stmtFindAntrian->fetch(PDO::FETCH_ASSOC);
            if ($rm_data && $rm_data['id_antrian']) {
                $stmtUpdateAntrian = $pdo->prepare("UPDATE Antrian SET status = 'Menunggu Pembayaran' WHERE id_antrian = ?");
                $stmtUpdateAntrian->execute([$rm_data['id_antrian']]);
            }

            $msg = "<div class='mb-6 p-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl font-medium flex items-center gap-2'>
                <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'></path></svg> 
                Status resep berhasil diubah menjadi Selesai.
            </div>";
        }
    }
}

// Fetch Pending Prescriptions (Menunggu Racikan)
$today = date('Y-m-d');
$stmtResep = $pdo->prepare("
    SELECT r.id_resep, r.tgl_resep, rm.nomor_RM, p.nama_pasien, p.jenis_kelamin, p.umur_pasien, d.nama_lengkap as nama_dokter
    FROM Resep r
    JOIN Rekam_Medis rm ON r.nomor_RM = rm.nomor_RM
    JOIN Pasien p ON rm.id_pasien = p.id_pasien
    JOIN Pengguna d ON r.id_dokter = d.id_user
    WHERE DATE(r.tgl_resep) = ? AND r.status = 'Menunggu Racikan'
    ORDER BY r.id_resep ASC
");
$stmtResep->execute([$today]);
$reseps = $stmtResep->fetchAll(PDO::FETCH_ASSOC);

// Fetch details for each prescription
$resepDetails = [];
if (!empty($reseps)) {
    $ids = array_column($reseps, 'id_resep');
    $inQuery = implode(',', array_fill(0, count($ids), '?'));
    
    $stmtDetail = $pdo->prepare("
        SELECT dr.id_resep, dr.jumlah, dr.dosis, o.nama_obat, o.jenis_obat
        FROM Detail_Resep dr
        JOIN Obat o ON dr.id_obat = o.id_obat
        WHERE dr.id_resep IN ($inQuery)
    ");
    $stmtDetail->execute($ids);
    $details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($details as $d) {
        $resepDetails[$d['id_resep']][] = $d;
    }
}
?>

<?= $msg ?>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col min-h-[600px]">
    <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 class="font-bold text-slate-800">Antrian Resep (Menunggu Racikan)</h3>
        <div class="text-sm font-medium text-slate-500"><?= date('d F Y') ?></div>
    </div>
    
    <div class="flex-1 p-6">
        <?php if(empty($reseps)): ?>
            <div class="flex flex-col items-center justify-center h-64 text-slate-400">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <p class="font-medium text-lg text-slate-500">Tidak ada antrian resep yang menunggu.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach($reseps as $r): ?>
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                    <div class="bg-indigo-50/50 p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h4 class="font-bold text-lg text-indigo-900"><?= htmlspecialchars($r['nama_pasien']) ?></h4>
                                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded-md">ID Resep: RSP-<?= str_pad($r['id_resep'], 4, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            <div class="text-sm text-slate-600 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <?= $r['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>, <?= htmlspecialchars($r['umur_pasien'] ?? '-') ?> Tahun
                                <span class="text-slate-300">|</span>
                                <span class="font-medium">Dokter: <?= htmlspecialchars($r['nama_dokter']) ?></span>
                            </div>
                        </div>
                        <form method="POST" action="index.php?tab=antrian_resep" class="w-full sm:w-auto">
                            <input type="hidden" name="action" value="selesaikan">
                            <input type="hidden" name="id_resep" value="<?= $r['id_resep'] ?>">
                            <button type="submit" onclick="return confirm('Tandai resep ini telah selesai diracik?');" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Konfirmasi Selesai
                            </button>
                        </form>
                    </div>
                    
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left text-sm min-w-[600px]">
                            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Nama Obat</th>
                                    <th class="px-6 py-3 font-semibold">Jenis</th>
                                    <th class="px-6 py-3 font-semibold w-24">Jumlah</th>
                                    <th class="px-6 py-3 font-semibold">Dosis / Aturan Pakai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if(isset($resepDetails[$r['id_resep']])): ?>
                                    <?php foreach($resepDetails[$r['id_resep']] as $obat): ?>
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-6 py-3 font-medium text-slate-800"><?= htmlspecialchars($obat['nama_obat']) ?></td>
                                        <td class="px-6 py-3 text-slate-600"><?= htmlspecialchars($obat['jenis_obat']) ?></td>
                                        <td class="px-6 py-3 font-medium text-slate-700"><?= htmlspecialchars($obat['jumlah']) ?></td>
                                        <td class="px-6 py-3 text-slate-600"><?= htmlspecialchars($obat['dosis']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-slate-400">Tidak ada rincian obat.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// views/dokter/antrian.php

$id_dokter = $_SESSION['user_id'];
$today = date('Y-m-d');

// Fetch antrian khusus untuk dokter yang login
$stmtQueue = $pdo->prepare("
    SELECT a.id_antrian, a.nomor_antrian, a.status, p.id_pasien, p.nama_pasien, p.jenis_kelamin, TIMESTAMPDIFF(YEAR, p.tempat_tanggal_lahir, CURDATE()) as umur 
    FROM Antrian a
    JOIN Pasien p ON a.id_pasien = p.id_pasien
    WHERE a.tgl_antrian = ? AND a.id_dokter = ? AND a.status = 'Menunggu'
    ORDER BY a.id_antrian ASC
");
$stmtQueue->execute([$today, $id_dokter]);
$queueList = $stmtQueue->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col min-h-[600px]">
    <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 class="font-bold text-slate-800">Antrian Pasien Saya Hari Ini</h3>
        <div class="text-sm font-medium text-slate-500"><?= date('d F Y') ?></div>
    </div>
    <div class="flex-1 p-6">
        <?php if(empty($queueList)): ?>
            <div class="flex flex-col items-center justify-center h-64 text-slate-400">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="font-medium text-lg text-slate-500">Hore! Tidak ada antrian yang menunggu.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($queueList as $q): ?>
                    <div class="border border-slate-200 rounded-xl p-5 hover:border-blue-400 hover:shadow-md transition-all group bg-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-blue-100 text-blue-700 font-bold px-3 py-1 rounded-bl-lg text-sm border-l border-b border-blue-200">
                            <?= htmlspecialchars($q['nomor_antrian']) ?>
                        </div>
                        <h4 class="font-bold text-lg text-slate-800 mb-1 pr-12"><?= htmlspecialchars($q['nama_pasien']) ?></h4>
                        <div class="flex items-center gap-4 text-sm text-slate-500 mb-6">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <?= $q['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <?= htmlspecialchars($q['umur'] ?? '-') ?> Tahun
                            </span>
                        </div>
                        
                        <a href="index.php?tab=emr&id_antrian=<?= $q['id_antrian'] ?>" class="block w-full text-center bg-blue-50 text-blue-600 font-semibold py-2 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors border border-blue-200 group-hover:border-blue-600">
                            Periksa Pasien
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// views/resepsionis/pendaftaran.php

$msg = '';

// Handle Registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $tgl_lahir = $_POST['tgl_lahir'] ?? '';
    $jk = $_POST['jenis_kelamin'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $id_dokter = $_POST['id_dokter'] ?? '';
    
    if($nama_lengkap && $tgl_lahir && $jk && $id_dokter) {
        // 1. Insert Pasien
        $stmtPasien = $pdo->prepare("INSERT INTO Pasien (nama_pasien, tempat_tanggal_lahir, jenis_kelamin, alamat_pasien) VALUES (?, ?, ?, ?)");
        $stmtPasien->execute([$nama_lengkap, $tgl_lahir, $jk, $alamat]);
        $id_pasien = $pdo->lastInsertId();
        
        // 2. Create Antrian
        $today = date('Y-m-d');
        $stmtAntri = $pdo->prepare("SELECT COUNT(*) as total FROM Antrian WHERE tgl_antrian = ?");
        $stmtAntri->execute([$today]);
        $count = $stmtAntri->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Generate nomor antrian (e.g., A001)
        $no_antrian = 'A' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        
        $stmtInsertAntri = $pdo->prepare("INSERT INTO Antrian (id_pasien, id_dokter, nomor_antrian, tgl_antrian, status) VALUES (?, ?, ?, ?, 'Menunggu')");
        $stmtInsertAntri->execute([$id_pasien, $id_dokter, $no_antrian, $today]);
        
        $msg = "<div class='mb-4 p-3 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg'>Berhasil registrasi pasien. Nomor Antrian: <b>$no_antrian</b></div>";
    } else {
        $msg = "<div class='mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-lg'>Gagal: Data tidak lengkap.</div>";
    }
}

// Fetch Doctors for dropdown
$stmtDokter = $pdo->prepare("SELECT id_user, nama_lengkap, spesialis FROM Pengguna WHERE role = 'Dokter'");
$stmtDokter->execute();
$dokters = $stmtDokter->fetchAll(PDO::FETCH_ASSOC);

// Fetch Queue list for today
$today = date('Y-m-d');
$stmtQueue = $pdo->prepare("
    SELECT a.id_antrian, a.nomor_antrian, a.status, p.nama_pasien, d.nama_lengkap as nama_dokter 
    FROM Antrian a
    JOIN Pasien p ON a.id_pasien = p.id_pasien
    JOIN Pengguna d ON a.id_dokter = d.id_user
    WHERE a.tgl_antrian = ?
    ORDER BY a.id_antrian DESC
");
$stmtQueue->execute([$today]);
$queueList = $stmtQueue->fetchAll(PDO::FETCH_ASSOC);
?>

<?= $msg ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-[600px]">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-800">Daftar Antrian Hari Ini</h3>
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" placeholder="Cari nama pasien..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm w-full md:w-64 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
        </div>
        <div class="flex-1 overflow-auto p-4">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 sticky top-0">
                    <tr>
                        <th class="p-3 font-semibold rounded-tl-lg">No. Antrian</th>
                        <th class="p-3 font-semibold">Nama Pasien</th>
                        <th class="p-3 font-semibold">Dokter Tujuan</th>
                        <th class="p-3 font-semibold">Status</th>
                        <th class="p-3 font-semibold rounded-tr-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($queueList)): ?>
                    <tr><td colspan="5" class="p-4 text-center text-slate-500">Belum ada antrian hari ini.</td></tr>
                    <?php else: ?>
                        <?php foreach($queueList as $q): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="p-3 font-bold text-slate-700"><?= htmlspecialchars($q['nomor_antrian']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($q['nama_pasien']) ?></td>
                            <td class="p-3 text-slate-600"><?= htmlspecialchars($q['nama_dokter']) ?></td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap
                                    <?= $q['status'] === 'Menunggu' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' ?>">
                                    <?= htmlspecialchars($q['status']) ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <a href="index.php?tab=edit_antrian&id=<?= $q['id_antrian'] ?>" class="inline-block text-blue-600 hover:text-blue-800 font-medium text-xs bg-blue-50 px-3 py-1.5 rounded-lg transition-colors">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 h-fit">
        <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-6">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Registrasi Pasien Baru
        </h3>
        <form action="index.php?tab=pendaftaran" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="register">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Masukkan nama..." required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Tgl Lahir</label>
                    <input type="date" name="tgl_lahir" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Alamat</label>
                <textarea name="alamat" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Masukkan alamat pasien..." required></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Poliklinik / Dokter Tujuan</label>
                <select name="id_dokter" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                    <option value="">-- Pilih Dokter --</option>
                    <?php foreach($dokters as $dok): ?>
                        <option value="<?= $dok['id_user'] ?>">
                            <?= htmlspecialchars($dok['nama_lengkap']) ?> (<?= htmlspecialchars($dok['spesialis'] ?? 'Umum') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg text-sm mt-4 transition-colors shadow-sm shadow-blue-200">
                Cetak Nomor Antrian
            </button>
        </form>
    </div>
</div>

<?php
// views/resepsionis/edit_antrian.php
require_once 'includes/db.php';

$id_antrian = $_GET['id'] ?? null;
$msg = '';

if (!$id_antrian) {
    echo '<div class="p-4 bg-red-50 text-red-600 rounded-lg">ID Antrian tidak valid.</div>';
    return;
}

// Process Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_antrian') {
    $id_pasien = $_POST['id_pasien'] ?? '';
    
    // Patient data
    $nama = $_POST['nama_lengkap'] ?? '';
    $tgl_lahir = $_POST['tgl_lahir'] ?? '';
    $jk = $_POST['jenis_kelamin'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    
    // Queue data
    $id_dokter = $_POST['id_dokter'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if($nama && $tgl_lahir && $jk && $id_dokter && $status && $id_pasien) {
        try {
            $pdo->beginTransaction();
            
            // 1. Update Pasien
            $stmtUpdatePasien = $pdo->prepare("UPDATE Pasien SET nama_pasien = ?, tempat_tanggal_lahir = ?, jenis_kelamin = ?, alamat_pasien = ? WHERE id_pasien = ?");
            $stmtUpdatePasien->execute([$nama, $tgl_lahir, $jk, $alamat, $id_pasien]);
            
            // 2. Update Antrian
            $stmtUpdateAntrian = $pdo->prepare("UPDATE Antrian SET id_dokter = ?, status = ? WHERE id_antrian = ?");
            $stmtUpdateAntrian->execute([$id_dokter, $status, $id_antrian]);
            
            $pdo->commit();
            $msg = "<div class='mb-6 p-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl flex items-center gap-3'>
                        <svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                        <span class='font-medium'>Berhasil memperbarui data antrian dan pasien.</span>
                    </div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "<div class='mb-6 p-4 bg-red-50 text-red-700 border border-red-200 rounded-xl'>Gagal memperbarui data: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $msg = "<div class='mb-6 p-4 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl'>Peringatan: Pastikan semua data terisi dengan benar.</div>";
    }
}

// Fetch current queue & patient details
$stmt = $pdo->prepare("
    SELECT a.*, p.nama_pasien, p.tempat_tanggal_lahir, p.jenis_kelamin, p.alamat_pasien 
    FROM Antrian a
    JOIN Pasien p ON a.id_pasien = p.id_pasien
    WHERE a.id_antrian = ?
");
$stmt->execute([$id_antrian]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo '<div class="p-4 bg-red-50 text-red-600 rounded-lg">Data antrian tidak ditemukan.</div>';
    return;
}

// Fetch Doctors
$stmtDokter = $pdo->prepare("SELECT id_user, nama_lengkap, spesialis FROM Pengguna WHERE role = 'Dokter'");
$stmtDokter->execute();
$dokters = $stmtDokter->fetchAll(PDO::FETCH_ASSOC);

// Available Statuses
$statuses = ['Menunggu', 'Diperiksa', 'Menunggu Obat', 'Selesai', 'Batal'];
?>

<div class="max-w-4xl mx-auto">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4 mb-6">
        <a href="index.php?tab=pendaftaran" class="w-10 h-10 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-slate-50 shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-slate-800">Edit Antrian & Data Pasien</h2>
            <p class="text-sm text-slate-500">Nomor Antrian: <span class="font-bold text-slate-700"><?= htmlspecialchars($data['nomor_antrian']) ?></span></p>
        </div>
    </div>

    <?= $msg ?>

    <form action="index.php?tab=edit_antrian&id=<?= $id_antrian ?>" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <input type="hidden" name="action" value="edit_antrian">
        <input type="hidden" name="id_pasien" value="<?= $data['id_pasien'] ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 p-8">
            
            <!-- Patient Data Section -->
            <div class="space-y-5">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-2 border-b border-slate-100 pb-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Informasi Pasien
                </h3>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_pasien']) ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tgl Lahir</label>
                        <input type="date" name="tgl_lahir" value="<?= htmlspecialchars($data['tempat_tanggal_lahir']) ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                            <option value="L" <?= $data['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= $data['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                    <textarea name="alamat" rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required><?= htmlspecialchars($data['alamat_pasien']) ?></textarea>
                </div>
            </div>

            <!-- Queue Data Section -->
            <div class="space-y-5">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-2 border-b border-slate-100 pb-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Detail Antrian
                </h3>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Dokter Tujuan</label>
                    <select name="id_dokter" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        <option value="">-- Pilih Dokter --</option>
                        <?php foreach($dokters as $dok): ?>
                            <option value="<?= $dok['id_user'] ?>" <?= $data['id_dokter'] == $dok['id_user'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dok['nama_lengkap']) ?> (<?= htmlspecialchars($dok['spesialis'] ?? 'Umum') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Antrian</label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-medium focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        <?php foreach($statuses as $st): ?>
                            <option value="<?= $st ?>" <?= $data['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-slate-500 mt-2">
                        Pilih <b>Batal</b> jika pasien membatalkan kunjungan. Pilih <b>Selesai</b> hanya jika proses di klinik telah tuntas.
                    </p>
                </div>
            </div>

        </div>

        <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
            <a href="index.php?tab=pendaftaran" class="px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-lg hover:bg-slate-100 transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-sm transition-colors">Simpan Perubahan</button>
        </div>
    </form>
</div>

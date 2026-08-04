<?php
// views/dokter/emr.php

$msg = '';
$id_dokter = $_SESSION['user_id'];
$id_antrian = $_GET['id_antrian'] ?? '';

if (!$id_antrian) {
    echo "<div class='p-6 bg-white rounded-xl shadow-sm text-center text-slate-500'>Silakan pilih pasien dari menu Antrian terlebih dahulu.</div>";
    return;
}

// Fetch Antrian & Pasien Data
$stmt = $pdo->prepare("
    SELECT a.*, p.nama_pasien, p.jenis_kelamin, TIMESTAMPDIFF(YEAR, p.tempat_tanggal_lahir, CURDATE()) as umur, p.berat_badan, p.tinggi_badan, p.alamat_pasien 
    FROM Antrian a
    JOIN Pasien p ON a.id_pasien = p.id_pasien
    WHERE a.id_antrian = ? AND a.id_dokter = ?
");
$stmt->execute([$id_antrian, $id_dokter]);
$pasien = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pasien) {
    echo "<div class='p-6 bg-white rounded-xl shadow-sm text-center text-red-500'>Data antrian tidak ditemukan atau tidak valid.</div>";
    return;
}



// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_emr') {
    $keluhan = $_POST['keluhan'] ?? '';
    $fisik = $_POST['fisik'] ?? '';
    $diagnosis = $_POST['diagnosis'] ?? '';
    $tindakan = $_POST['tindakan'] ?? '';
    $berat_badan = $_POST['berat_badan'] ?? null;
    $tinggi_badan = $_POST['tinggi_badan'] ?? null;
    
    // Arrays for drugs
    $id_obats = $_POST['id_obat'] ?? [];
    $jumlahs = $_POST['jumlah'] ?? [];
    $dosis_list = $_POST['dosis'] ?? [];

    try {
        $pdo->beginTransaction();

        // 0. Update Berat/Tinggi Pasien
        if ($berat_badan !== null || $tinggi_badan !== null) {
            // Keep existing values if empty to avoid wiping them out if they are not filled
            $bb_val = $berat_badan ?: $pasien['berat_badan'];
            $tb_val = $tinggi_badan ?: $pasien['tinggi_badan'];
            $stmtUpdatePasien = $pdo->prepare("UPDATE Pasien SET berat_badan = ?, tinggi_badan = ? WHERE id_pasien = ?");
            $stmtUpdatePasien->execute([$bb_val, $tb_val, $pasien['id_pasien']]);
        }

        // 1. Insert Rekam_Medis (needs to add id_antrian column in DB, or just omit if not in SQL)
        // Wait, Rekam_Medis in SQL doesn't have id_antrian or pemeriksaan_fisik/diagnosa_masuk exactly as wireframe.
        // Let's check original SQL: nomor_RM, id_pasien, id_dokter, tgl_masuk, alasan_masuk, pemeriksaan_fisik, pemeriksaan_penunjang, diagnosa_masuk, tindakan, ruang, cara_keluar
        
        $stmtRM = $pdo->prepare("INSERT INTO Rekam_Medis (id_pasien, id_dokter, tgl_masuk, alasan_masuk, pemeriksaan_fisik, diagnosa_masuk, tindakan, id_antrian) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)");
        $stmtRM->execute([$pasien['id_pasien'], $id_dokter, $keluhan, $fisik, $diagnosis, $tindakan, $id_antrian]);
        $nomor_RM = $pdo->lastInsertId();

        // 2. Handle E-Resep if there are any drugs
        if (!empty($id_obats)) {
            $stmtResep = $pdo->prepare("INSERT INTO Resep (nomor_RM, id_dokter) VALUES (?, ?)");
            $stmtResep->execute([$nomor_RM, $id_dokter]);
            $id_resep = $pdo->lastInsertId();

            $stmtDetail = $pdo->prepare("INSERT INTO Detail_Resep (id_resep, id_obat, jumlah, dosis) VALUES (?, ?, ?, ?)");
            for ($i = 0; $i < count($id_obats); $i++) {
                $stmtDetail->execute([$id_resep, $id_obats[$i], $jumlahs[$i], $dosis_list[$i]]);
            }
        }

        // 3. Update Antrian Status
        if (!empty($id_obats)) {
            $stmtUpdate = $pdo->prepare("UPDATE Antrian SET status = 'Menunggu Obat' WHERE id_antrian = ?");
        } else {
            $stmtUpdate = $pdo->prepare("UPDATE Antrian SET status = 'Menunggu Pembayaran' WHERE id_antrian = ?");
        }
        $stmtUpdate->execute([$id_antrian]);

        $pdo->commit();
        $msg = "<div class='mb-6 p-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl font-medium flex items-center gap-2'><svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'></path></svg> Data Rekam Medis & Resep berhasil disimpan.</div>";
        
        // Hide form after success
        $saved = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = "<div class='mb-6 p-4 bg-red-50 text-red-700 border border-red-200 rounded-xl font-medium'>Gagal menyimpan data: " . $e->getMessage() . "</div>";
    }
}

// Fetch all medicines for dropdown
$stmtObat = $pdo->query("SELECT * FROM Obat ORDER BY nama_obat ASC");
$obatList = $stmtObat->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($msg)) echo $msg; ?>

<?php if (!isset($saved)): ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Kolom Kiri: Form Rekam Medis (SOAP) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Pemeriksaan Klinis (SOAP)
                </h3>
            </div>
            <div class="p-6">
                <form id="emrForm" action="index.php?tab=emr&id_antrian=<?= $id_antrian ?>" method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="save_emr">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Subjective -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Subjective (Keluhan)</label>
                            <textarea name="keluhan" rows="3" class="w-full border border-slate-200 rounded-lg p-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-slate-50" placeholder="Riwayat dan keluhan pasien..." required></textarea>
                        </div>
                        
                        <!-- Objective (Physical & Vitals) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Objective (Fisik)</label>
                            <textarea name="fisik" rows="1" class="w-full border border-slate-200 rounded-lg p-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-slate-50 mb-3" placeholder="Hasil pemeriksaan fisik lainnya..." required></textarea>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="number" step="0.1" name="berat_badan" value="<?= $pasien['berat_badan'] ?>" class="w-full border border-slate-200 rounded-lg p-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-slate-50" placeholder="Berat (kg)">
                                </div>
                                <div>
                                    <input type="number" step="0.1" name="tinggi_badan" value="<?= $pasien['tinggi_badan'] ?>" class="w-full border border-slate-200 rounded-lg p-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-slate-50" placeholder="Tinggi (cm)">
                                </div>
                            </div>
                        </div>
                        <!-- Assessment -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Assessment (Diagnosis)</label>
                            <textarea name="diagnosis" rows="3" class="w-full border border-slate-200 rounded-lg p-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-slate-50" placeholder="Diagnosis penyakit..." required></textarea>
                        </div>
                        <!-- Plan -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Plan (Tindakan)</label>
                            <textarea name="tindakan" rows="3" class="w-full border border-slate-200 rounded-lg p-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-slate-50" placeholder="Rencana pengobatan/tindakan..." required></textarea>
                        </div>
                    </div>

                    <!-- E-Resep Builder (Hidden Inputs will be appended here by JS) -->
                    <div id="resepInputsContainer"></div>
                </form>
            </div>
        </div>
        
        <!-- E-Resep UI Builder -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
             <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-indigo-50/50">
                <h3 class="font-bold text-indigo-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    E-Resep Obat
                </h3>
            </div>
            <div class="p-6">
                <!-- Add Drug Controls -->
                <div class="flex gap-3 mb-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Pilih Obat</label>
                        <select id="obatSelect" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                            <option value="">-- Pilih Obat --</option>
                            <?php foreach($obatList as $o): ?>
                                <option value="<?= $o['id_obat'] ?>" data-nama="<?= htmlspecialchars($o['nama_obat']) ?>">
                                    <?= htmlspecialchars($o['nama_obat']) ?> (<?= htmlspecialchars($o['jenis_obat']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Jumlah</label>
                        <input type="number" id="obatJumlah" min="1" value="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Dosis/Aturan</label>
                        <input type="text" id="obatDosis" placeholder="e.g., 3 x 1 Sesudah Makan" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button type="button" id="btnAddObat" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors h-[38px]">Tambah</button>
                    </div>
                </div>

                <!-- Selected Drugs Table -->
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="p-3 rounded-tl-lg font-medium">Nama Obat</th>
                            <th class="p-3 font-medium w-24">Jumlah</th>
                            <th class="p-3 font-medium">Aturan Pakai</th>
                            <th class="p-3 rounded-tr-lg font-medium w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="resepTableBody">
                        <!-- Rows will be injected by JS -->
                        <tr id="emptyResepRow"><td colspan="4" class="p-4 text-center text-slate-400">Belum ada obat yang ditambahkan.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex justify-end gap-4 mt-6">
            <a href="index.php?tab=antrian" class="px-6 py-2.5 rounded-lg border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors">Batal</a>
            <button type="button" onclick="document.getElementById('emrForm').submit();" class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-sm shadow-blue-200 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Simpan Rekam Medis
            </button>
        </div>
    </div>

    <!-- Kolom Kanan: Profil Pasien -->
    <div class="h-fit">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden sticky top-28">
            <div class="bg-slate-900 p-6 flex flex-col items-center text-white relative">
                <div class="absolute top-4 right-4 bg-blue-500 px-3 py-1 rounded-full text-xs font-bold shadow-lg shadow-blue-900/50">
                    <?= htmlspecialchars($pasien['nomor_antrian']) ?>
                </div>
                <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center text-3xl font-bold mb-4 border-4 border-slate-700">
                    <?= strtoupper(substr($pasien['nama_pasien'], 0, 1)) ?>
                </div>
                <h3 class="text-xl font-bold mb-1"><?= htmlspecialchars($pasien['nama_pasien']) ?></h3>
                <p class="text-slate-400 text-sm"><?= $pasien['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?> &bull; <?= $pasien['umur'] ?? '-' ?> Tahun</p>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Berat / Tinggi</p>
                    <p class="text-slate-700 font-medium"><?= $pasien['berat_badan'] ?? '-' ?> kg / <?= $pasien['tinggi_badan'] ?? '-' ?> cm</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Alamat</p>
                    <p class="text-slate-700 font-medium"><?= htmlspecialchars($pasien['alamat_pasien'] ?? '-') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Logic for dynamic E-Resep Builder
let resepItems = [];

document.getElementById('btnAddObat').addEventListener('click', function() {
    const select = document.getElementById('obatSelect');
    const jumlah = document.getElementById('obatJumlah').value;
    const dosis = document.getElementById('obatDosis').value;
    
    if(!select.value) {
        alert("Pilih obat terlebih dahulu!");
        return;
    }
    if(!dosis) {
        alert("Aturan pakai/dosis harus diisi!");
        return;
    }
    
    const namaObat = select.options[select.selectedIndex].getAttribute('data-nama');
    const idObat = select.value;
    
    resepItems.push({ id: idObat, nama: namaObat, jumlah: jumlah, dosis: dosis });
    
    // Reset inputs
    select.value = '';
    document.getElementById('obatJumlah').value = 1;
    document.getElementById('obatDosis').value = '';
    
    renderResepTable();
});

function removeObat(index) {
    resepItems.splice(index, 1);
    renderResepTable();
}

function renderResepTable() {
    const tbody = document.getElementById('resepTableBody');
    const container = document.getElementById('resepInputsContainer');
    
    tbody.innerHTML = '';
    container.innerHTML = '';
    
    if(resepItems.length === 0) {
        tbody.innerHTML = '<tr id="emptyResepRow"><td colspan="4" class="p-4 text-center text-slate-400">Belum ada obat yang ditambahkan.</td></tr>';
        return;
    }
    
    resepItems.forEach((item, index) => {
        // Table row
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-100 bg-white';
        tr.innerHTML = `
            <td class="p-3 font-medium text-slate-700">${item.nama}</td>
            <td class="p-3">${item.jumlah}</td>
            <td class="p-3 text-slate-600">${item.dosis}</td>
            <td class="p-3">
                <button type="button" onclick="removeObat(${index})" class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        
        // Hidden inputs for form submission
        container.innerHTML += `
            <input type="hidden" name="id_obat[]" value="${item.id}">
            <input type="hidden" name="jumlah[]" value="${item.jumlah}">
            <input type="hidden" name="dosis[]" value="${item.dosis}">
        `;
    });
}
</script>
<?php endif; ?>

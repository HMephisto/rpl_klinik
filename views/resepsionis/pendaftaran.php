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
    $id_pasien_lama = $_POST['id_pasien_lama'] ?? ''; // Existing patient ID (if returning)
    
    if($id_pasien_lama && $id_dokter) {
        // --- PASIEN LAMA: Reuse existing patient, only create Antrian ---
        $id_pasien = $id_pasien_lama;

        $today = date('Y-m-d');
        $stmtAntri = $pdo->prepare("SELECT COUNT(*) as total FROM Antrian WHERE tgl_antrian = ?");
        $stmtAntri->execute([$today]);
        $count = $stmtAntri->fetch(PDO::FETCH_ASSOC)['total'];

        $no_antrian = 'A' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $stmtInsertAntri = $pdo->prepare("INSERT INTO Antrian (id_pasien, id_dokter, nomor_antrian, tgl_antrian, status) VALUES (?, ?, ?, ?, 'Menunggu')");
        $stmtInsertAntri->execute([$id_pasien, $id_dokter, $no_antrian, $today]);

        $msg = "<div class='mb-4 p-3 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg'>Berhasil mendaftarkan pasien lama. Nomor Antrian: <b>$no_antrian</b></div>";
    } elseif($nama_lengkap && $tgl_lahir && $jk && $id_dokter) {
        // --- PASIEN BARU: Insert new patient + create Antrian ---
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
        
        $msg = "<div class='mb-4 p-3 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg'>Berhasil registrasi pasien baru. Nomor Antrian: <b>$no_antrian</b></div>";
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
    
    <!-- Registration Panel -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden h-fit">
        <!-- Toggle: Pasien Baru / Pasien Lama -->
        <div class="flex border-b border-slate-200">
            <button type="button" id="tabBaru" onclick="switchTab('baru')" class="flex-1 py-3.5 text-sm font-semibold text-center transition-colors border-b-2 border-blue-600 text-blue-600 bg-blue-50/50">
                <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Pasien Baru
            </button>
            <button type="button" id="tabLama" onclick="switchTab('lama')" class="flex-1 py-3.5 text-sm font-semibold text-center transition-colors border-b-2 border-transparent text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Pasien Lama
            </button>
        </div>

        <!-- ============================================ -->
        <!-- FORM: PASIEN BARU (default, shown first)     -->
        <!-- ============================================ -->
        <div id="formBaru" class="p-6">
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

        <!-- ============================================ -->
        <!-- FORM: PASIEN LAMA (hidden by default)        -->
        <!-- ============================================ -->
        <div id="formLama" class="p-6 hidden">
            <!-- Search Input -->
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Cari Pasien</label>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="searchPasienInput" placeholder="Ketik nama pasien (min. 2 huruf)..." 
                           class="w-full pl-9 pr-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" autocomplete="off">
                </div>
            </div>

            <!-- Search Results -->
            <div id="searchResults" class="space-y-2 mb-4 max-h-[240px] overflow-y-auto">
                <!-- Results will be injected here -->
            </div>

            <!-- Empty state -->
            <div id="searchEmpty" class="hidden text-center py-6">
                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 20a8 8 0 100-16 8 8 0 000 16z"></path></svg>
                <p class="text-sm text-slate-400">Pasien tidak ditemukan.</p>
                <button type="button" onclick="switchTab('baru')" class="mt-2 text-xs text-blue-600 hover:text-blue-800 font-medium">Daftarkan sebagai Pasien Baru →</button>
            </div>

            <!-- Selected Patient Card + Dokter Form -->
            <div id="selectedPatientPanel" class="hidden">
                <div id="selectedPatientCard" class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4">
                    <!-- Filled dynamically -->
                </div>
                <form action="index.php?tab=pendaftaran" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="id_pasien_lama" id="idPasienLama" value="">
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
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors shadow-sm shadow-emerald-200">
                        Cetak Nomor Antrian
                    </button>
                </form>
                <button type="button" onclick="clearSelection()" class="w-full mt-2 text-xs text-slate-400 hover:text-slate-600 py-1 transition-colors">
                    ← Pilih pasien lain
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ========== Tab Switching ==========
function switchTab(tab) {
    const tabBaru = document.getElementById('tabBaru');
    const tabLama = document.getElementById('tabLama');
    const formBaru = document.getElementById('formBaru');
    const formLama = document.getElementById('formLama');

    if (tab === 'baru') {
        formBaru.classList.remove('hidden');
        formLama.classList.add('hidden');
        tabBaru.classList.add('border-blue-600', 'text-blue-600', 'bg-blue-50/50');
        tabBaru.classList.remove('border-transparent', 'text-slate-400');
        tabLama.classList.remove('border-blue-600', 'text-blue-600', 'bg-blue-50/50');
        tabLama.classList.add('border-transparent', 'text-slate-400');
    } else {
        formLama.classList.remove('hidden');
        formBaru.classList.add('hidden');
        tabLama.classList.add('border-blue-600', 'text-blue-600', 'bg-blue-50/50');
        tabLama.classList.remove('border-transparent', 'text-slate-400');
        tabBaru.classList.remove('border-blue-600', 'text-blue-600', 'bg-blue-50/50');
        tabBaru.classList.add('border-transparent', 'text-slate-400');
        // Focus search input
        setTimeout(() => document.getElementById('searchPasienInput').focus(), 100);
    }
}

// ========== Debounced Search ==========
let searchTimeout = null;
const searchInput = document.getElementById('searchPasienInput');
const searchResults = document.getElementById('searchResults');
const searchEmpty = document.getElementById('searchEmpty');
const selectedPanel = document.getElementById('selectedPatientPanel');

searchInput.addEventListener('input', function() {
    const query = this.value.trim();
    clearTimeout(searchTimeout);

    if (query.length < 2) {
        searchResults.innerHTML = '';
        searchEmpty.classList.add('hidden');
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch('search_pasien.php?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                searchResults.innerHTML = '';
                searchEmpty.classList.add('hidden');
                selectedPanel.classList.add('hidden');

                if (data.length === 0) {
                    searchEmpty.classList.remove('hidden');
                    return;
                }

                data.forEach(p => {
                    const jk = p.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
                    const tgl = p.tempat_tanggal_lahir || '-';
                    const alamat = p.alamat_pasien || '-';

                    const card = document.createElement('div');
                    card.className = 'border border-slate-200 rounded-lg p-3 cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all group';
                    card.innerHTML = `
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm text-slate-800 group-hover:text-blue-700 transition-colors">${escapeHtml(p.nama_pasien)}</p>
                                <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-1">
                                    <span class="text-xs text-slate-500">
                                        <svg class="w-3 h-3 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        ${escapeHtml(tgl)}
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        <svg class="w-3 h-3 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        ${escapeHtml(jk)}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 mt-1 truncate">
                                    <svg class="w-3 h-3 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    ${escapeHtml(alamat)}
                                </p>
                            </div>
                            <span class="shrink-0 text-xs text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity font-medium mt-0.5">Pilih</span>
                        </div>
                    `;
                    card.addEventListener('click', () => selectPatient(p));
                    searchResults.appendChild(card);
                });
            })
            .catch(err => {
                console.error('Search error:', err);
                searchResults.innerHTML = '<p class="text-xs text-red-500 text-center py-2">Gagal mencari data.</p>';
            });
    }, 300);
});

// ========== Select Patient ==========
function selectPatient(p) {
    const jk = p.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    const tgl = p.tempat_tanggal_lahir || '-';
    const alamat = p.alamat_pasien || '-';

    document.getElementById('idPasienLama').value = p.id_pasien;

    document.getElementById('selectedPatientCard').innerHTML = `
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-sm font-bold text-emerald-800">Pasien Terpilih</span>
        </div>
        <p class="font-semibold text-slate-800">${escapeHtml(p.nama_pasien)}</p>
        <div class="flex flex-wrap gap-x-4 gap-y-0.5 mt-1 text-xs text-slate-600">
            <span>🗓 ${escapeHtml(tgl)}</span>
            <span>👤 ${escapeHtml(jk)}</span>
        </div>
        <p class="text-xs text-slate-500 mt-1">📍 ${escapeHtml(alamat)}</p>
    `;

    // Show selected panel, hide search results
    searchResults.innerHTML = '';
    searchEmpty.classList.add('hidden');
    searchInput.value = '';
    selectedPanel.classList.remove('hidden');
}

// ========== Clear Selection ==========
function clearSelection() {
    selectedPanel.classList.add('hidden');
    document.getElementById('idPasienLama').value = '';
    searchInput.focus();
}

// ========== Utility ==========
function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
}
</script>

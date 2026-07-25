<?php
// views/apoteker/obat.php

// Handle form submissions
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $nama_obat = $_POST['nama_obat'] ?? '';
            $jenis_obat = $_POST['jenis_obat'] ?? '';
            $harga = $_POST['harga'] ?? 0;
            
            if ($nama_obat && $jenis_obat) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO Obat (nama_obat, jenis_obat, harga) VALUES (?, ?, ?)");
                    $stmt->execute([$nama_obat, $jenis_obat, $harga]);
                    $success_msg = "Obat berhasil ditambahkan.";
                } catch (PDOException $e) {
                    $error_msg = "Gagal menambahkan obat: " . $e->getMessage();
                }
            } else {
                $error_msg = "Semua field harus diisi.";
            }
        } elseif ($action === 'edit') {
            $id_obat = $_POST['id_obat'] ?? '';
            $nama_obat = $_POST['nama_obat'] ?? '';
            $jenis_obat = $_POST['jenis_obat'] ?? '';
            $harga = $_POST['harga'] ?? 0;
            
            if ($id_obat && $nama_obat && $jenis_obat) {
                try {
                    $stmt = $pdo->prepare("UPDATE Obat SET nama_obat = ?, jenis_obat = ?, harga = ? WHERE id_obat = ?");
                    $stmt->execute([$nama_obat, $jenis_obat, $harga, $id_obat]);
                    $success_msg = "Obat berhasil diperbarui.";
                } catch (PDOException $e) {
                    $error_msg = "Gagal memperbarui obat: " . $e->getMessage();
                }
            } else {
                $error_msg = "Semua field harus diisi.";
            }
        } elseif ($action === 'delete') {
            $id_obat = $_POST['id_obat'] ?? '';
            
            if ($id_obat) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM Obat WHERE id_obat = ?");
                    $stmt->execute([$id_obat]);
                    $success_msg = "Obat berhasil dihapus.";
                } catch (PDOException $e) {
                    $error_msg = "Gagal menghapus obat (mungkin sedang digunakan dalam resep): " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch all medicines
$stmt = $pdo->query("SELECT * FROM Obat ORDER BY nama_obat ASC");
$obatList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col min-h-[600px]">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <div>
            <h3 class="font-bold text-slate-800 text-lg">Manajemen Obat</h3>
            <p class="text-sm text-slate-500">Kelola daftar obat yang tersedia di klinik</p>
        </div>
        <button onclick="openModal('add')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Obat Baru
        </button>
    </div>

    <div class="p-6 flex-1">
        <?php if ($success_msg): ?>
            <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-lg border border-green-200 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-medium border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">ID Obat</th>
                        <th class="py-3 px-4">Nama Obat</th>
                        <th class="py-3 px-4">Jenis Obat</th>
                        <th class="py-3 px-4 text-right">Harga</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($obatList)): ?>
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                Belum ada data obat.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($obatList as $obat): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4 font-medium text-slate-700">#<?= htmlspecialchars($obat['id_obat']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($obat['nama_obat']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        <?= htmlspecialchars($obat['jenis_obat']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">Rp <?= number_format($obat['harga'] ?? 0, 0, ',', '.') ?></td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="openModal('edit', <?= htmlspecialchars(json_encode($obat)) ?>)" class="text-blue-600 hover:text-blue-800 p-1.5 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button onclick="confirmDelete(<?= $obat['id_obat'] ?>, '<?= htmlspecialchars(addslashes($obat['nama_obat'])) ?>')" class="text-red-600 hover:text-red-800 p-1.5 bg-red-50 hover:bg-red-100 rounded-md transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div id="obatModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center overflow-y-auto">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 shadow-xl relative transform transition-all">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-2xl">
            <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Tambah Obat Baru</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors p-1 bg-white hover:bg-slate-100 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form method="POST" action="index.php?tab=obat" class="p-6">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id_obat" id="formIdObat" value="">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Obat <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_obat" id="formNamaObat" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Obat <span class="text-red-500">*</span></label>
                    <input type="text" name="jenis_obat" id="formJenisObat" required placeholder="Cth: Tablet, Sirup, Kapsul" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Harga (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="harga" id="formHarga" required placeholder="0" min="0" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400 text-sm">
                </div>
            </div>
            
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-5 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg font-medium transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors shadow-sm shadow-blue-500/30">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteForm" method="POST" action="index.php?tab=obat" class="hidden">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id_obat" id="deleteIdObat" value="">
</form>

<script>
    const modal = document.getElementById('obatModal');
    const modalTitle = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const formIdObat = document.getElementById('formIdObat');
    const formNamaObat = document.getElementById('formNamaObat');
    const formJenisObat = document.getElementById('formJenisObat');
    const formHarga = document.getElementById('formHarga');

    function openModal(action, data = null) {
        if (action === 'add') {
            modalTitle.textContent = 'Tambah Obat Baru';
            formAction.value = 'add';
            formIdObat.value = '';
            formNamaObat.value = '';
            formJenisObat.value = '';
            formHarga.value = '';
        } else if (action === 'edit' && data) {
            modalTitle.textContent = 'Edit Obat';
            formAction.value = 'edit';
            formIdObat.value = data.id_obat;
            formNamaObat.value = data.nama_obat;
            formJenisObat.value = data.jenis_obat;
            formHarga.value = data.harga || 0;
        }
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    function confirmDelete(id, nama) {
        if (confirm(`Apakah Anda yakin ingin menghapus obat "${nama}"? Data yang sudah dihapus tidak dapat dikembalikan.`)) {
            document.getElementById('deleteIdObat').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
</script>

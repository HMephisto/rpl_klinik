<?php
// views/resepsionis/kasir.php
$msg = '';

// Handle Payment Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bayar') {
    $id_antrian = $_POST['id_antrian'] ?? '';
    $id_pasien = $_POST['id_pasien'] ?? '';
    $total_harga = $_POST['total_harga'] ?? 0;
    $rincian_json = $_POST['rincian_json'] ?? '';
    $id_staff = $_SESSION['user_id'] ?? 0;
    
    if ($id_antrian && $id_pasien) {
        try {
            $pdo->beginTransaction();
            
            // Insert to Struk
            $stmtStruk = $pdo->prepare("INSERT INTO Struk (id_pasien, id_staff, total_harga, pemeriksaan_penunjang, tgl_bayar) VALUES (?, ?, ?, ?, NOW())");
            $stmtStruk->execute([$id_pasien, $id_staff, $total_harga, $rincian_json]);
            
            // Update Antrian
            $stmtUpdate = $pdo->prepare("UPDATE Antrian SET status = 'Selesai' WHERE id_antrian = ?");
            $stmtUpdate->execute([$id_antrian]);
            
            $pdo->commit();
            $msg = "<div class='mb-6 p-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl font-medium flex items-center gap-2'>
                <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'></path></svg> 
                Pembayaran berhasil diproses dan pasien ditandai selesai.
            </div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "<div class='mb-6 p-4 bg-red-50 text-red-700 border border-red-200 rounded-xl font-medium'>Gagal memproses pembayaran: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch Pending Payments
$stmtKasir = $pdo->query("
    SELECT a.id_antrian, a.id_pasien, a.tgl_antrian, p.nama_pasien, d.nama_lengkap as nama_dokter, rm.nomor_RM
    FROM Antrian a
    JOIN Pasien p ON a.id_pasien = p.id_pasien
    JOIN Pengguna d ON a.id_dokter = d.id_user
    LEFT JOIN Rekam_Medis rm ON a.id_antrian = rm.id_antrian
    WHERE a.status = 'Menunggu Pembayaran'
    ORDER BY a.id_antrian ASC
");
$pending_payments = $stmtKasir->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals for each
$billing_data = [];
foreach ($pending_payments as $row) {
    $total_obat = 0;
    $rincian_obat = [];
    $obats = []; // Initialize to prevent data bleeding between patients
    
    if ($row['nomor_RM']) {
        // Find resep for this RM
        $stmtResep = $pdo->prepare("
            SELECT o.nama_obat, o.harga, dr.jumlah 
            FROM Resep r
            JOIN Detail_Resep dr ON r.id_resep = dr.id_resep
            JOIN Obat o ON dr.id_obat = o.id_obat
            WHERE r.nomor_RM = ?
        ");
        $stmtResep->execute([$row['nomor_RM']]);
        $obats = $stmtResep->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($obats as $obat) {
            $subtotal = $obat['harga'] * $obat['jumlah'];
            $total_obat += $subtotal;
            $rincian_obat[] = $obat['nama_obat'] . " (" . $obat['jumlah'] . "x)";
        }
    }
    
    $tarif = defined('TARIF_KONSULTASI') ? TARIF_KONSULTASI : 50000;
    $total_biaya = $tarif + $total_obat;
    
    $rincian_text = "Konsultasi (" . $row['nama_dokter'] . ")";
    if (count($rincian_obat) > 0) {
        $rincian_text .= " + Obat: " . implode(", ", $rincian_obat);
    }
    
    // Build JSON for detail itemization
    $itemized = [];
    $itemized[] = [
        'nama' => 'Konsultasi (' . $row['nama_dokter'] . ')',
        'qty' => 1,
        'harga' => $tarif,
        'subtotal' => $tarif
    ];
    if (isset($obats)) {
        foreach ($obats as $obat) {
            $itemized[] = [
                'nama' => 'Obat: ' . $obat['nama_obat'],
                'qty' => $obat['jumlah'],
                'harga' => $obat['harga'],
                'subtotal' => $obat['harga'] * $obat['jumlah']
            ];
        }
    }
    $rincian_json_str = json_encode($itemized);
    
    $billing_data[] = [
        'id_antrian' => $row['id_antrian'],
        'id_pasien' => $row['id_pasien'],
        'invoice_no' => 'INV-' . date('Ymd') . '-' . str_pad($row['id_antrian'], 4, '0', STR_PAD_LEFT),
        'nama_pasien' => $row['nama_pasien'],
        'rincian' => $rincian_text,
        'rincian_json' => $rincian_json_str,
        'total_biaya' => $total_biaya
    ];
}
?>

<?= $msg ?>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col min-h-[600px]">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 class="font-bold text-slate-800 text-lg">Menunggu Pembayaran</h3>
        <div class="text-sm font-medium text-slate-500"><?= date('d F Y') ?></div>
    </div>
    <div class="flex-1 p-6">
        <?php if(empty($billing_data)): ?>
            <div class="flex flex-col items-center justify-center h-64 text-slate-400">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p class="font-medium text-lg text-slate-500">Tidak ada pasien yang menunggu pembayaran.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[800px]">
                    <thead class="text-slate-500 text-sm border-b border-slate-200">
                        <tr>
                            <th class="pb-3 font-semibold">No. Invoice</th>
                            <th class="pb-3 font-semibold">Nama Pasien</th>
                            <th class="pb-3 font-semibold w-1/3">Rincian</th>
                            <th class="pb-3 font-semibold text-right pr-8">Total Biaya</th>
                            <th class="pb-3 font-semibold text-center w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($billing_data as $bill): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors align-top">
                            <td class="py-4 font-bold text-slate-700"><?= htmlspecialchars($bill['invoice_no']) ?></td>
                            <td class="py-4 font-medium text-slate-800"><?= htmlspecialchars($bill['nama_pasien']) ?></td>
                            <td class="py-4 text-sm text-slate-500 leading-relaxed"><?= htmlspecialchars($bill['rincian']) ?></td>
                            <td class="py-4 font-bold text-right text-emerald-600 text-lg whitespace-nowrap pr-8">Rp <?= number_format($bill['total_biaya'], 0, ',', '.') ?></td>
                            <td class="py-4 text-center">
                                <form method="POST" action="index.php?tab=kasir" onsubmit="return confirm('Proses pembayaran untuk pasien ini?');">
                                    <input type="hidden" name="action" value="bayar">
                                    <input type="hidden" name="id_antrian" value="<?= $bill['id_antrian'] ?>">
                                    <input type="hidden" name="id_pasien" value="<?= $bill['id_pasien'] ?>">
                                    <input type="hidden" name="total_harga" value="<?= $bill['total_biaya'] ?>">
                                    <input type="hidden" name="rincian_json" value='<?= htmlspecialchars($bill['rincian_json'], ENT_QUOTES) ?>'>
                                    <button type="submit" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200 hover:border-emerald-600 px-4 py-2 rounded-lg text-sm font-semibold transition-colors inline-flex items-center justify-center gap-2 w-full">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Tandai Lunas
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

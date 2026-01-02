<?php
include 'layout/header.php';

$id_santri = $_GET['id_santri'] ?? null;
if (!$id_santri) {
    echo "<script>window.location='tagihan.php';</script>";
    exit;
}

// Fetch Santri Info
$q_santri = "SELECT s.*, u.nama_unit 
              FROM santri s 
              JOIN units u ON s.id_unit = u.id 
              WHERE s.id = '$id_santri'";
$res_santri = mysqli_query($koneksi, $q_santri);
$santri = mysqli_fetch_assoc($res_santri);

if (!$santri) {
    echo "<script>window.location='tagihan.php';</script>";
    exit;
}

// Fetch All Bills for this Student
$q_bills = "SELECT t.*, (t.nominal - t.terbayar) as sisa
             FROM tagihan t 
             WHERE t.id_santri = '$id_santri'
             ORDER BY t.tanggal_dibuat DESC";
$res_bills = mysqli_query($koneksi, $q_bills);

// Fetch Total Summary
$q_summary = "SELECT SUM(nominal) as total_nominal, SUM(terbayar) as total_terbayar FROM tagihan WHERE id_santri = '$id_santri'";
$res_summary = mysqli_query($koneksi, $q_summary);
$summary = mysqli_fetch_assoc($res_summary);
$total_sisa = $summary['total_nominal'] - $summary['total_terbayar'];

?>

<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="tagihan.php" class="text-gray-500 hover:text-emerald-600 transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali Ke Daftar
        </a>
        <div class="flex gap-2">
            <!-- Rekap Print across all bills -->
            <a href="kuitansi_cetak_all.php?id_santri=<?= $id_santri ?>" target="_blank"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors flex items-center shadow-sm">
                <i class="fas fa-print mr-2"></i> Cetak Rekap Semua
            </a>
        </div>
    </div>

    <!-- Student Info Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-8">
        <div class="bg-emerald-600 p-6 text-white flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-bold uppercase"><?= $santri['nama'] ?></h3>
                <p class="text-emerald-100">NIS: <?= $santri['nis'] ?> | Kelas: <?= $santri['kelas'] ?>
                    (<?= $santri['nama_unit'] ?>)</p>
            </div>
            <div class="text-right hidden md:block">
                <p class="text-xs text-emerald-200 uppercase tracking-widest font-bold">Status Keuangan</p>
                <span class="text-xl font-bold"><?= $total_sisa == 0 ? 'LUNAS' : 'MENUNGGAK' ?></span>
            </div>
        </div>
        <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-8 text-center bg-gray-50">
            <div>
                <p class="text-gray-500 text-sm mb-1">Total Kewajiban</p>
                <h4 class="text-2xl font-bold text-gray-800">Rp
                    <?= number_format($summary['total_nominal'], 0, ',', '.') ?></h4>
            </div>
            <div>
                <p class="text-gray-500 text-sm mb-1">Total Terbayar</p>
                <h4 class="text-2xl font-bold text-emerald-600">Rp
                    <?= number_format($summary['total_terbayar'], 0, ',', '.') ?></h4>
            </div>
            <div>
                <p class="text-gray-500 text-sm mb-1">Total Kekurangan</p>
                <h4 class="text-2xl font-bold text-red-600">Rp <?= number_format($total_sisa, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>

    <!-- Detailed Bills List -->
    <h3 class="text-lg font-bold text-gray-800 mb-4 px-2">Rincian Tagihan</h3>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b">
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Item Tagihan</th>
                    <th class="p-4 text-right">Nominal</th>
                    <th class="p-4 text-right">Terbayar</th>
                    <th class="p-4 text-right">Sisa</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php while ($b = mysqli_fetch_assoc($res_bills)): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-sm text-gray-600"><?= date('d/m/y', strtotime($b['tanggal_dibuat'])) ?></td>
                        <td class="p-4 text-sm font-bold text-gray-800"><?= $b['judul'] ?></td>
                        <td class="p-4 text-right text-sm">Rp <?= number_format($b['nominal'], 0, ',', '.') ?></td>
                        <td class="p-4 text-right text-sm text-emerald-600">Rp
                            <?= number_format($b['terbayar'], 0, ',', '.') ?></td>
                        <td class="p-4 text-right text-sm font-bold text-red-500">Rp
                            <?= number_format($b['sisa'], 0, ',', '.') ?></td>
                        <td class="p-4 text-center">
                            <?php if ($b['status'] == 'Lunas'): ?>
                                <span
                                    class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-bold">LUNAS</span>
                            <?php elseif ($b['status'] == 'Sebagian'): ?>
                                <span
                                    class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-bold">DICICIL</span>
                            <?php else: ?>
                                <span
                                    class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold">BELUM</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <?php if ($b['status'] != 'Lunas'): ?>
                                    <a href="tagihan_bayar.php?id=<?= $b['id'] ?>"
                                        class="text-emerald-600 hover:text-emerald-800 transition-colors"
                                        title="Bayar Item Ini">
                                        <i class="fas fa-wallet"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="kuitansi_cetak.php?tagihan_id=<?= $b['id'] ?>" target="_blank"
                                    class="text-gray-400 hover:text-blue-600 transition-colors"
                                    title="Cetak Riwayat Item Ini">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Individual Payment History -->
    <h3 class="text-lg font-bold text-gray-800 mb-4 px-2">Riwayat Pembayaran Terakhir</h3>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-500 text-xs uppercase tracking-wider border-b">
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Untuk Tagihan</th>
                        <th class="p-4">Keterangan</th>
                        <th class="p-4 text-right">Jumlah Bayar</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php
                    $q_hist = "SELECT pt.*, t.judul, tr.tanggal as tgl_bayar 
                               FROM pembayaran_tagihan pt 
                               JOIN tagihan t ON pt.id_tagihan = t.id 
                               JOIN transaksi tr ON pt.id_transaksi = tr.id
                               WHERE t.id_santri = '$id_santri' 
                               ORDER BY tr.tanggal DESC LIMIT 10";
                    $res_hist = mysqli_query($koneksi, $q_hist);
                    while ($h = mysqli_fetch_assoc($res_hist)):
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 text-sm text-gray-600"><?= date('d/m/Y H:i', strtotime($h['tgl_bayar'])) ?></td>
                            <td class="p-4 text-sm font-medium"><?= $h['judul'] ?></td>
                            <td class="p-4 text-xs text-gray-500 italic"><?= $h['keterangan'] ?: '-' ?></td>
                            <td class="p-4 text-right font-bold text-emerald-600 text-sm">Rp
                                <?= number_format($h['jumlah'], 0, ',', '.') ?></td>
                            <td class="p-4 text-center">
                                <a href="kuitansi_cetak.php?pembayaran_id=<?= $h['id'] ?>" target="_blank"
                                    class="text-gray-400 hover:text-emerald-600 transition-colors">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (mysqli_num_rows($res_hist) == 0): ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-300 text-sm italic">Belum ada transaksi
                                pembayaran.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
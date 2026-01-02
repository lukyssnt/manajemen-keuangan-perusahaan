<?php
include 'layout/header.php';

$role = $_SESSION['role'];
$id_unit_session = $_SESSION['id_unit'];

// Default Dates: First and Last day of current month
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');

// Unit Filter
if ($role == 'super_admin') {
    $id_unit_filter = $_GET['id_unit'] ?? 'all'; // Default all for admin or specific
} else {
    $id_unit_filter = $id_unit_session;
}

// Build Query
$where = "WHERE t.tanggal BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'";

if ($id_unit_filter != 'all') {
    $where .= " AND t.id_unit = '$id_unit_filter'";
}

$query = "SELECT t.*, u.nama_unit 
          FROM transaksi t 
          JOIN units u ON t.id_unit = u.id 
          $where 
          ORDER BY t.tanggal ASC";

$result = mysqli_query($koneksi, $query);

// Calculate Summary
$total_masuk = 0;
$total_keluar = 0;
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <h2 class="text-3xl font-bold text-gray-800">Laporan Keuangan</h2>
</div>

<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-6">
    <form action="" method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <?php if ($role == 'super_admin'): ?>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Unit</label>
                <select name="id_unit"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
                    <option value="all" <?= $id_unit_filter == 'all' ? 'selected' : '' ?>>Semua Unit</option>
                    <option value="1" <?= $id_unit_filter == '1' ? 'selected' : '' ?>>Putra</option>
                    <option value="2" <?= $id_unit_filter == '2' ? 'selected' : '' ?>>Putri</option>
                </select>
            </div>
        <?php endif; ?>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Tanggal Awal</label>
            <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>"
                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
        </div>
        <div>
            <label class="block text-gray-700 font-bold mb-2">Tanggal Akhir</label>
            <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>"
                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center justify-center flex-1">
                <i class="fas fa-filter mr-2"></i> Tampilkan
            </button>
            <a href="laporan_cetak.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&id_unit=<?= $id_unit_filter ?>"
                target="_blank"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center justify-center">
                <i class="fas fa-print"></i>
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-semibold">No</th>
                    <th class="p-4 font-semibold">Tanggal</th>
                    <th class="p-4 font-semibold">Keterangan</th>
                    <th class="p-4 font-semibold text-right">Masuk</th>
                    <th class="p-4 font-semibold text-right">Keluar</th>
                    <?php if ($role == 'super_admin' && $id_unit_filter == 'all'): ?>
                        <th class="p-4 font-semibold">Unit</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)):
                    if ($row['jenis'] == 'Masuk') {
                        $total_masuk += $row['nominal'];
                        $masuk = $row['nominal'];
                        $keluar = 0;
                    } else {
                        $total_keluar += $row['nominal'];
                        $masuk = 0;
                        $keluar = $row['nominal'];
                    }
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-gray-500">
                            <?= $no++ ?>
                        </td>
                        <td class="p-4 text-gray-600">
                            <?= date('d/m/Y', strtotime($row['tanggal'])) ?>
                        </td>
                        <td class="p-4 font-medium text-gray-800">
                            <?= $row['keterangan'] ?>
                        </td>
                        <td class="p-4 text-right text-emerald-600 font-bold">
                            <?= $masuk > 0 ? 'Rp ' . number_format($masuk, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="p-4 text-right text-red-600 font-bold">
                            <?= $keluar > 0 ? 'Rp ' . number_format($keluar, 0, ',', '.') : '-' ?>
                        </td>
                        <?php if ($role == 'super_admin' && $id_unit_filter == 'all'): ?>
                            <td class="p-4 text-gray-500">
                                <?= $row['nama_unit'] ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>

                <?php if ($no == 1): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">Tidak ada data transaksi pada periode ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="bg-gray-50 font-bold text-gray-800">
                <tr>
                    <td colspan="3" class="p-4 text-right">TOTAL</td>
                    <td class="p-4 text-right text-emerald-700">Rp
                        <?= number_format($total_masuk, 0, ',', '.') ?>
                    </td>
                    <td class="p-4 text-right text-red-700">Rp
                        <?= number_format($total_keluar, 0, ',', '.') ?>
                    </td>
                    <?php if ($role == 'super_admin' && $id_unit_filter == 'all'): ?>
                        <td></td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td colspan="3" class="p-4 text-right">SURPLUS / DEFISIT</td>
                    <td colspan="2"
                        class="p-4 text-center <?= ($total_masuk - $total_keluar) >= 0 ? 'text-emerald-700' : 'text-red-700' ?>">
                        Rp
                        <?= number_format($total_masuk - $total_keluar, 0, ',', '.') ?>
                    </td>
                    <?php if ($role == 'super_admin' && $id_unit_filter == 'all'): ?>
                        <td></td>
                    <?php endif; ?>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
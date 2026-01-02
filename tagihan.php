<?php
include 'layout/header.php';

$role = $_SESSION['role'];
$id_unit_session = $_SESSION['id_unit'];

// Filter Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'Belum Lunas';

$where = "WHERE 1=1";

if ($role != 'super_admin') {
    $where .= " AND t.id_unit = '$id_unit_session'";
}

if ($search) {
    $where .= " AND (s.nama LIKE '%$search%' OR s.nis LIKE '%$search%')";
}

// Group by Santri to show summary per student
$query = "SELECT s.id as id_santri, s.nama as nama_santri, s.kelas, u.nama_unit, 
                 SUM(t.nominal) as total_tagihan, 
                 SUM(t.terbayar) as total_terbayar,
                 (SUM(t.nominal) - SUM(t.terbayar)) as sisa_tagihan,
                 COUNT(t.id) as jml_judul_tagihan
          FROM tagihan t 
          JOIN santri s ON t.id_santri = s.id 
          JOIN units u ON t.id_unit = u.id 
          $where 
          GROUP BY s.id 
          HAVING 1=1";

if ($status_filter == 'Lunas') {
    $query .= " AND sisa_tagihan = 0";
} elseif ($status_filter == 'Belum Lunas') {
    $query .= " AND sisa_tagihan > 0";
}

$query .= " ORDER BY s.nama ASC";

$result = mysqli_query($koneksi, $query);
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <h2 class="text-3xl font-bold text-gray-800">Data Tagihan</h2>

    <div class="flex gap-2 w-full md:w-auto">
        <a href="tagihan_buat.php"
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-lg">
            <i class="fas fa-plus mr-2"></i> Buat Tagihan
        </a>
    </div>
</div>

<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
    <form action="" method="get" class="flex flex-col md:flex-row gap-4">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Santri / Tagihan..."
            class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
        <select name="status" class="border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Semua Status</option>
            <option value="Belum Lunas" <?= $status_filter == 'Belum Lunas' ? 'selected' : '' ?>>Belum Lunas</option>
            <option value="Lunas" <?= $status_filter == 'Lunas' ? 'selected' : '' ?>>Lunas</option>
        </select>
        <button type="submit"
            class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-6 py-2 rounded-lg transition-colors">Filter</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-semibold">Santri</th>
                    <th class="p-4 font-semibold text-center">Jml Tagihan</th>
                    <th class="p-4 font-semibold text-right">Total Tagihan</th>
                    <th class="p-4 font-semibold text-right">Terbayar</th>
                    <th class="p-4 font-semibold text-right text-red-600">Kekurangan</th>
                    <th class="p-4 font-semibold text-center">Status</th>
                    <th class="p-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-4">
                        <div class="font-bold text-gray-800"><?= $row['nama_santri'] ?></div>
                        <div class="text-xs text-gray-500"><?= $row['kelas'] ?> - <?= $row['nama_unit'] ?></div>
                    </td>
                    <td class="p-4 text-center text-sm font-medium text-gray-600">
                        <?= $row['jml_judul_tagihan'] ?> Item
                    </td>
                    <td class="p-4 text-right font-medium">Rp <?= number_format($row['total_tagihan'], 0, ',', '.') ?></td>
                    <td class="p-4 text-right text-emerald-600">Rp <?= number_format($row['total_terbayar'], 0, ',', '.') ?></td>
                    <td class="p-4 text-right font-bold text-red-600">Rp <?= number_format($row['sisa_tagihan'], 0, ',', '.') ?></td>
                    <td class="p-4 text-center">
                        <?php if ($row['sisa_tagihan'] == 0): ?>
                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">Lunas</span>
                        <?php elseif ($row['total_terbayar'] > 0): ?>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold">Sebagian</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">Belum Lunas</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <a href="tagihan_detail.php?id_santri=<?= $row['id_santri'] ?>" class="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-blue-700 transition-colors shadow-sm inline-flex items-center gap-2">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                 <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-400">Data tagihan tidak ditemukan.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
<?php
include 'layout/header.php';

$role = $_SESSION['role'];
$id_unit_session = $_SESSION['id_unit'];

// Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Base Filter for Alumni
$where = "WHERE s.status = 'Lulus'";
if ($role != 'super_admin') {
    $where .= " AND s.id_unit = '$id_unit_session'";
}

if ($search) {
    $where .= " AND (s.nama LIKE '%$search%' OR s.nis LIKE '%$search%')";
}

$query = "SELECT s.*, u.nama_unit, 
                 IFNULL(SUM(t.nominal - t.terbayar), 0) as sisa_tagihan
          FROM santri s 
          JOIN units u ON s.id_unit = u.id 
          LEFT JOIN tagihan t ON s.id = t.id_santri
          $where 
          GROUP BY s.id 
          ORDER BY s.nama ASC";

$result = mysqli_query($koneksi, $query);
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4 animate-fade-in-up">
    <div>
        <h2 class="text-3xl font-bold text-gray-800">Data dan Tagihan Alumni</h2>
        <p class="text-gray-500 text-sm">Santri yang sudah lulus dan sisa tagihannya.</p>
    </div>

    <div class="flex flex-wrap gap-2 w-full md:w-auto">
        <a href="santri_import.php"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-sm text-sm font-bold">
            <i class="fas fa-file-import mr-2"></i> Import
        </a>
        <a href="alumni_export.php"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-sm text-sm font-bold">
            <i class="fas fa-file-export mr-2"></i> Export
        </a>
        <a href="santri.php"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors flex items-center shadow-sm border text-sm font-bold">
            <i class="fas fa-users mr-2"></i> Aktif
        </a>
        <form action="" method="get" class="flex-1 md:w-48 relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Nama/NIS..."
                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-emerald-500 transition-colors">
        </form>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-semibold">NIS</th>
                    <th class="p-4 font-semibold">Nama Alumni</th>
                    <th class="p-4 font-semibold">Terakhir di Kelas</th>
                    <?php if ($role == 'super_admin'): ?>
                        <th class="p-4 font-semibold">Unit</th>
                    <?php endif; ?>
                    <th class="p-4 font-semibold text-right">Sisa Tagihan</th>
                    <th class="p-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-gray-500">
                            <?= $row['nis'] ?>
                        </td>
                        <td class="p-4 font-medium text-gray-800">
                            <?= $row['nama'] ?>
                        </td>
                        <td class="p-4 text-gray-600">
                            <?= $row['kelas'] ?>
                        </td>
                        <?php if ($role == 'super_admin'): ?>
                            <td class="p-4 text-gray-500">
                                <?= $row['nama_unit'] ?>
                            </td>
                        <?php endif; ?>
                        <td
                            class="p-4 text-right font-bold <?= $row['sisa_tagihan'] > 0 ? 'text-red-500' : 'text-emerald-500' ?>">
                            Rp
                            <?= number_format($row['sisa_tagihan'], 0, ',', '.') ?>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="tagihan_detail.php?id_santri=<?= $row['id'] ?>"
                                    class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-sm hover:bg-blue-200 transition-colors shadow-sm"
                                    title="Lihat Tagihan Alumni">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> Detail Tagihan
                                </a>
                                <a href="santri_edit.php?id=<?= $row['id'] ?>"
                                    class="text-emerald-600 hover:text-emerald-800 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">Data alumni tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
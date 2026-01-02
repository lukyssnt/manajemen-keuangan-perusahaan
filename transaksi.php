<?php
require_once 'config/koneksi.php';

$id_unit_session = $_SESSION['id_unit'];
$role = $_SESSION['role'];

// Delete Logic
if (isset($_GET['delete'])) {
    check_csrf_get();

    $id_del = mysqli_real_escape_string($koneksi, $_GET['delete']);

    // Fetch Transaction Details for Rollback
    $q_check = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id='$id_del'");
    $row_del = mysqli_fetch_assoc($q_check);

    if ($row_del) {
        // Security Check
        if ($role != 'super_admin' && $row_del['id_unit'] != $id_unit_session) {
            header("Location: transaksi.php?msg=error");
            exit;
        }

        // Rollback Saldo
        $nominal = $row_del['nominal'];
        $unit_target = $row_del['id_unit'];

        if ($row_del['jenis'] == 'Masuk') {
            // If it was income, remove it -> Decrease Saldo
            $q_saldo = "UPDATE rekening SET saldo = saldo - $nominal WHERE id_unit = '$unit_target'";
        } else {
            // If it was expense, remove it -> Increase Saldo
            $q_saldo = "UPDATE rekening SET saldo = saldo + $nominal WHERE id_unit = '$unit_target'";
        }

        mysqli_query($koneksi, $q_saldo);
        mysqli_query($koneksi, "DELETE FROM transaksi WHERE id = '$id_del'");

        log_audit("Menghapus transaksi ID $id_del (" . $row_del['jenis'] . ") senilai " . format_rupiah($nominal));

        header("Location: transaksi.php?msg=deleted");
        exit;
    }
}

// Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$where_clause = "";

// Base Filter by Unit
if ($role != 'super_admin') {
    $where_clause = "WHERE t.id_unit = '$id_unit_session'";
}

// Search Filter
if ($search) {
    if ($where_clause) {
        $where_clause .= " AND (t.keterangan LIKE '%$search%')";
    } else {
        $where_clause = "WHERE (t.keterangan LIKE '%$search%' OR u.nama_unit LIKE '%$search%')";
    }
}

// Order by
$order_clause = "ORDER BY t.tanggal DESC";

$query = "SELECT t.*, u.nama_unit, us.username as cashier_name
          FROM transaksi t 
          JOIN units u ON t.id_unit = u.id 
          LEFT JOIN users us ON t.id_user = us.id
          $where_clause 
          $order_clause";

$result = mysqli_query($koneksi, $query);

// Summary Calculation for Widgets
if ($role == 'super_admin') {
    $q_sum = mysqli_query($koneksi, "SELECT 
        SUM(CASE WHEN jenis = 'Masuk' THEN nominal ELSE 0 END) as total_masuk,
        SUM(CASE WHEN jenis = 'Keluar' THEN nominal ELSE 0 END) as total_keluar
        FROM transaksi");
    $q_saldo = mysqli_query($koneksi, "SELECT SUM(saldo) as total_saldo FROM rekening");
} else {
    $q_sum = mysqli_query($koneksi, "SELECT 
        SUM(CASE WHEN jenis = 'Masuk' THEN nominal ELSE 0 END) as total_masuk,
        SUM(CASE WHEN jenis = 'Keluar' THEN nominal ELSE 0 END) as total_keluar
        FROM transaksi WHERE id_unit = '$id_unit_session'");
    $q_saldo = mysqli_query($koneksi, "SELECT saldo as total_saldo FROM rekening WHERE id_unit = '$id_unit_session'");
}

$sum_data = mysqli_fetch_assoc($q_sum);
$saldo_data = mysqli_fetch_assoc($q_saldo);

$total_masuk = $sum_data['total_masuk'] ?? 0;
$total_keluar = $sum_data['total_keluar'] ?? 0;
$total_saldo = $total_masuk - $total_keluar; // Use calculated instead of table for better sync

include 'layout/header.php';
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4 animate-fade-in-up">
    <h2 class="text-3xl font-bold text-gray-800">Transaksi Kas</h2>

    <div class="flex gap-2 w-full md:w-auto">
        <form action="" method="get" class="flex-1 md:w-64 relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Keterangan..."
                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-emerald-500 transition-colors">
        </form>
        <a href="transaksi_tambah.php"
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-lg">
            <i class="fas fa-plus mr-2"></i> Transaksi Baru
        </a>
    </div>
</div>

<!-- Summary Widgets -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div
        class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 animate-fade-in-up stagger-1 hover-card">
        <div class="bg-blue-50 p-4 rounded-lg text-blue-600">
            <i class="fas fa-wallet text-2xl"></i>
        </div>
        <div>
            <p class="text-gray-500 text-sm font-medium">Total Saldo Kas</p>
            <h3 class="text-2xl font-bold text-gray-800">Rp <?= number_format($total_saldo, 0, ',', '.') ?></h3>
        </div>
    </div>
    <div
        class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 animate-fade-in-up stagger-2 hover-card">
        <div class="bg-emerald-50 p-4 rounded-lg text-emerald-600">
            <i class="fas fa-arrow-down text-2xl"></i>
        </div>
        <div>
            <p class="text-gray-500 text-sm font-medium">Total Pemasukan</p>
            <h3 class="text-2xl font-bold text-emerald-600">Rp <?= number_format($total_masuk, 0, ',', '.') ?></h3>
        </div>
    </div>
    <div
        class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 animate-fade-in-up stagger-3 hover-card">
        <div class="bg-red-50 p-4 rounded-lg text-red-600">
            <i class="fas fa-arrow-up text-2xl"></i>
        </div>
        <div>
            <p class="text-gray-500 text-sm font-medium">Total Pengeluaran</p>
            <h3 class="text-2xl font-bold text-red-600">Rp <?= number_format($total_keluar, 0, ',', '.') ?></h3>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up stagger-4">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-semibold">Tgl</th>
                    <th class="p-4 font-semibold">Keterangan</th>
                    <th class="p-4 font-semibold">Jenis</th>
                    <th class="p-4 font-semibold text-right">Nominal</th>
                    <th class="p-4 font-semibold">Petugas</th>
                    <th class="p-4 font-semibold text-center">Nota</th>
                    <?php if ($role == 'super_admin'): ?>
                        <th class="p-4 font-semibold">Unit</th>
                    <?php endif; ?>
                    <th class="p-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php
                while ($row = mysqli_fetch_assoc($result)):
                    $is_masuk = $row['jenis'] == 'Masuk';
                    $color = $is_masuk ? 'text-emerald-600' : 'text-red-600';
                    $bg_badge = $is_masuk ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700';
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-gray-500 whitespace-nowrap">
                            <?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?>
                        </td>
                        <td class="p-4 font-medium text-gray-800">
                            <?= $row['keterangan'] ?>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 <?= $bg_badge ?> rounded text-xs font-bold uppercase">
                                <?= $row['jenis'] ?>
                            </span>
                        </td>
                        <td class="p-4 font-bold text-right <?= $color ?>">
                            <?= $is_masuk ? '+' : '-' ?> Rp
                            <?= number_format($row['nominal'], 0, ',', '.') ?>
                        </td>
                        <td class="p-4 text-gray-500 text-sm">
                            <?= ucfirst($row['cashier_name'] ?? '-') ?>
                        </td>
                        <td class="p-4 text-center">
                            <?php if ($row['nota']): ?>
                                <a href="uploads/nota/<?= $row['nota'] ?>" target="_blank"
                                    class="text-blue-500 hover:text-blue-700 transition-colors" title="Lihat Nota">
                                    <i class="fas fa-image"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-300">-</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($role == 'super_admin'): ?>
                            <td class="p-4 text-gray-500">
                                <span
                                    class="px-2 py-1 <?= $row['nama_unit'] == 'Putra' ? 'bg-blue-50 text-blue-600' : 'bg-pink-50 text-pink-600' ?> rounded text-xs font-bold">
                                    <?= $row['nama_unit'] ?>
                                </span>
                            </td>
                        <?php endif; ?>
                        <td class="p-4 text-center">
                            <a href="transaksi.php?delete=<?= $row['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                                onclick="return confirm('PERINGATAN: Menghapus transaksi akan mengembalikan sado kas. Lanjutkan?')"
                                class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-full h-8 w-8 flex items-center justify-center transition-colors inline-flex">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400">Belum ada data transaksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
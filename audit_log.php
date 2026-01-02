<?php
require_once 'config/koneksi.php';

if ($_SESSION['role'] != 'super_admin') {
    header("Location: index.php");
    exit;
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$where = "WHERE 1=1";

if ($search) {
    $where .= " AND (a.action LIKE '%$search%' OR u.username LIKE '%$search%' OR a.ip_address LIKE '%$search%')";
}

$query = "SELECT a.*, u.username, u.nama 
          FROM audit_log a 
          LEFT JOIN users u ON a.id_user = u.id 
          $where 
          ORDER BY a.tanggal DESC LIMIT 500";
$result = mysqli_query($koneksi, $query);

include 'layout/header.php';
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4 animate-fade-in-up">
    <div>
        <h2 class="text-3xl font-bold text-gray-800">Log Aktivitas Sistem</h2>
        <p class="text-gray-500 text-sm">Rekaman aktivitas pengguna untuk keamanan dan audit.</p>
    </div>

    <div class="flex gap-2 w-full md:w-auto">
        <form action="" method="get" class="flex-1 md:w-64 relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Log..."
                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-emerald-500 transition-colors">
        </form>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up stagger-1">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-semibold">Waktu</th>
                    <th class="p-4 font-semibold">Pengguna</th>
                    <th class="p-4 font-semibold">Aktivitas</th>
                    <th class="p-4 font-semibold">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-gray-500 text-sm italic">
                            <?= date('d/m/Y H:i:s', strtotime($row['tanggal'])) ?>
                        </td>
                        <td class="p-4">
                            <span class="font-bold text-gray-800">
                                <?= htmlspecialchars($row['nama'] ?: $row['username'] ?: 'System') ?>
                            </span>
                            <div class="text-xs text-gray-400">@
                                <?= htmlspecialchars($row['username'] ?: 'system') ?>
                            </div>
                        </td>
                        <td class="p-4 text-gray-700">
                            <?= htmlspecialchars($row['action']) ?>
                        </td>
                        <td class="p-4 text-gray-400 text-xs">
                            <?= htmlspecialchars($row['ip_address']) ?>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-400">Belum ada rekaman aktivitas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
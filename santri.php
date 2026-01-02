<?php
require_once 'config/koneksi.php';

// Delete Logic Check (MUST BE BEFORE HEADER)
if (isset($_GET['delete'])) {
    check_csrf_get();

    $role = $_SESSION['role'];
    $id_unit_session = $_SESSION['id_unit'];
    $id_del = mysqli_real_escape_string($koneksi, $_GET['delete']);

    // Fetch student name for logging
    $q_name = mysqli_query($koneksi, "SELECT nama FROM santri WHERE id='$id_del'");
    $r_name = mysqli_fetch_assoc($q_name);
    $nama_santri = $r_name['nama'] ?? 'Unknown';

    // Security Check: Unit Authorization
    if ($role != 'super_admin') {
        $check = mysqli_query($koneksi, "SELECT id FROM santri WHERE id='$id_del' AND id_unit='$id_unit_session'");
        if (mysqli_num_rows($check) == 0) {
            header("Location: santri.php?msg=error");
            exit;
        }
    }

    if (mysqli_query($koneksi, "DELETE FROM santri WHERE id = '$id_del'")) {
        log_audit("Menghapus data santri: $nama_santri (ID: $id_del)");
        header("Location: santri.php?msg=deleted");
        exit;
    }
}

include 'layout/header.php';
// ... rest of the fetch logic ...
$id_unit_session = $_SESSION['id_unit'];
$role = $_SESSION['role'];

// Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
// Base Filter by Unit & Status Aktif
$where = "WHERE s.status = 'Aktif'";
if ($role != 'super_admin') {
    $where .= " AND s.id_unit = '$id_unit_session'";
}

if ($search) {
    $where .= " AND (s.nama LIKE '%$search%' OR s.nis LIKE '%$search%' OR s.kelas LIKE '%$search%')";
}

// Order by
$order_clause = "ORDER BY s.kelas ASC, s.nama ASC";

$query = "SELECT s.*, u.nama_unit 
          FROM santri s 
          JOIN units u ON s.id_unit = u.id 
          $where 
          $order_clause";

$result = mysqli_query($koneksi, $query);
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4 animate-fade-in-up">
    <h2 class="text-3xl font-bold text-gray-800">Data Santri</h2>

    <div class="flex gap-2 w-full md:w-auto">
        <a href="alumni.php"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors flex items-center shadow-sm font-bold border"
            title="Lihat Alumni">
            <i class="fas fa-user-graduate mr-2"></i> Alumni
        </a>
        <form action="" method="get" class="flex-1 md:w-64 relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Nama/NIS..."
                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-emerald-500 transition-colors">
        </form>
        <a href="santri_import.php"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-lg"
            title="Import CSV">
            <i class="fas fa-file-import"></i>
        </a>
        <a href="santri_export.php"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-lg"
            title="Export Excel">
            <i class="fas fa-file-excel"></i>
        </a>
        <a href="santri_tambah.php"
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-lg">
            <i class="fas fa-plus mr-2"></i> Tambah
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up stagger-1">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-semibold">No</th>
                    <th class="p-4 font-semibold">NIS</th>
                    <th class="p-4 font-semibold">Nama Lengkap</th>
                    <th class="p-4 font-semibold">Kelas</th>
                    <?php if ($role == 'super_admin'): ?>
                        <th class="p-4 font-semibold">Unit</th>
                    <?php endif; ?>
                    <th class="p-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-gray-500">
                            <?= $no++ ?>
                        </td>
                        <td class="p-4 font-medium text-gray-800">
                            <?= $row['nis'] ?>
                        </td>
                        <td class="p-4 text-gray-800 font-semibold">
                            <?= $row['nama'] ?>
                        </td>
                        <td class="p-4 text-gray-600">
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded text-xs font-bold">
                                <?= $row['kelas'] ?>
                            </span>
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
                            <div class="flex justify-center gap-2">
                                <a href="santri_edit.php?id=<?= $row['id'] ?>"
                                    class="text-blue-500 hover:text-blue-700 bg-blue-50 p-2 rounded-full h-8 w-8 flex items-center justify-center transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id'] ?>)"
                                    class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-full h-8 w-8 flex items-center justify-center transition-colors">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">Data santri tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Data?',
            text: "Data santri yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-red-600 text-white px-4 py-2 rounded-lg mx-2',
                cancelButton: 'bg-gray-100 text-gray-700 px-4 py-2 rounded-lg mx-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'santri.php?delete=' + id + '&csrf_token=<?= $_SESSION['csrf_token'] ?>';
            }
        });
    }
</script>

<?php include 'layout/footer.php'; ?>
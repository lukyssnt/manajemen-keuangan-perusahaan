<?php
include 'layout/header.php';

// Access Control
if ($_SESSION['role'] != 'super_admin') {
    echo "<script>alert('Akses Ditolak!'); window.location='index.php';</script>";
    exit;
}

// Order by
$query = "SELECT u.*, un.nama_unit 
          FROM users u 
          LEFT JOIN units un ON u.id_unit = un.id 
          ORDER BY u.role ASC, u.username ASC";

$result = mysqli_query($koneksi, $query);

// Delete Logic
if (isset($_GET['delete'])) {
    $id_del = mysqli_real_escape_string($koneksi, $_GET['delete']);
    // Prevent deleting self
    if ($id_del == $_SESSION['id_user']) {
        header('Location: users.php?msg=error_self');
        exit;
    }
    
    mysqli_query($koneksi, "DELETE FROM users WHERE id = '$id_del'");
    header('Location: users.php?msg=deleted');
    exit;
}
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4 animate-fade-in-up">
    <div>
        <h2 class="text-3xl font-bold text-gray-800">Manajemen User</h2>
        <p class="text-gray-500 text-sm mt-1">Kelola hak akses administrator dan bendahara unit.</p>
    </div>
    
    <div class="flex gap-2">
        <a href="users_tambah.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl transition-all flex items-center shadow-lg hover:shadow-emerald-500/30 hover:-translate-y-1 font-medium">
            <i class="fas fa-plus mr-2"></i> Tambah User
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden animate-fade-in-up stagger-1">
    <div class="p-6">
        <table class="w-full text-left border-collapse" id="dataTable">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-200">
                    <th class="p-4 font-semibold rounded-tl-xl">No</th>
                    <th class="p-4 font-semibold">Username</th>
                    <th class="p-4 font-semibold">Role</th>
                    <th class="p-4 font-semibold">Unit Akses</th>
                    <th class="p-4 font-semibold text-center rounded-tr-xl">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr class="hover:bg-emerald-50/50 transition-colors group">
                    <td class="p-4"><?= $no++ ?></td>
                    <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($row['username']) ?></td>
                    <td class="p-4">
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wide border border-emerald-200"><?= str_replace('_', ' ', $row['role']) ?></span>
                    </td>
                    <td class="p-4">
                        <?php if ($row['role'] == 'super_admin'): ?>
                            <span class="text-xs italic text-gray-400 bg-gray-100 px-3 py-1 rounded-full border border-gray-200">Semua Akses</span>
                        <?php else: ?>
                            <span class="px-3 py-1 <?= $row['nama_unit'] == 'Putra' ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-pink-100 text-pink-700 border-pink-200' ?> rounded-full text-xs font-bold border"><?= htmlspecialchars($row['nama_unit']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">
                            <a href="users_edit.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-white hover:bg-blue-600 bg-blue-50 p-2 rounded-lg h-9 w-9 flex items-center justify-center transition-all shadow-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($row['id'] != $_SESSION['id_user']): ?>
                            <button onclick="confirmDelete('users.php?delete=<?= $row['id'] ?>')" class="text-red-600 hover:text-white hover:bg-red-600 bg-red-50 p-2 rounded-lg h-9 w-9 flex items-center justify-center transition-all shadow-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tambahan DataTables & SweetAlert Delete JS -->
<script>
    function confirmDelete(url) {
        Swal.fire({
            title: 'Hapus User?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-lg px-4 py-2',
                cancelButton: 'rounded-lg px-4 py-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }
</script>

<?php include 'layout/footer.php'; ?>

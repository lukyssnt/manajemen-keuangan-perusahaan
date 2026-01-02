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
        echo "<script>alert('Tidak bisa menghapus akun sendiri!'); window.location='users.php';</script>";
        exit;
    }
    
    mysqli_query($koneksi, "DELETE FROM users WHERE id = '$id_del'");
    echo "<script>alert('User berhasil dihapus'); window.location='users.php';</script>";
}
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <h2 class="text-3xl font-bold text-gray-800">Manajemen User</h2>
    
    <div class="flex gap-2">
        <a href="users_tambah.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-lg">
            <i class="fas fa-plus mr-2"></i> Tambah User
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-semibold">No</th>
                    <th class="p-4 font-semibold">Username</th>
                    <th class="p-4 font-semibold">Role</th>
                    <th class="p-4 font-semibold">Unit Akses</th>
                    <th class="p-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-4 text-gray-500"><?= $no++ ?></td>
                    <td class="p-4 font-medium text-gray-800"><?= $row['username'] ?></td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-bold uppercase"><?= str_replace('_', ' ', $row['role']) ?></span>
                    </td>
                    <td class="p-4 text-gray-500">
                        <?php if ($row['role'] == 'super_admin'): ?>
                            <span class="text-xs italic text-gray-400">Semua Akses</span>
                        <?php else: ?>
                            <span class="px-2 py-1 <?= $row['nama_unit'] == 'Putra' ? 'bg-blue-50 text-blue-600' : 'bg-pink-50 text-pink-600' ?> rounded text-xs font-bold"><?= $row['nama_unit'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="users_edit.php?id=<?= $row['id'] ?>" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-2 rounded-full h-8 w-8 flex items-center justify-center transition-colors">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($row['id'] != $_SESSION['id_user']): ?>
                            <a href="users.php?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-full h-8 w-8 flex items-center justify-center transition-colors">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

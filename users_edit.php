<?php
include 'layout/header.php';

// Access Control
if ($_SESSION['role'] != 'super_admin') {
    exit;
}

$id = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id'");
$row = mysqli_fetch_assoc($query);

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role = $_POST['role'];
    $id_unit = !empty($_POST['id_unit']) ? $_POST['id_unit'] : 'NULL';

    $sql_pass = "";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql_pass = ", password='$password'";
    }

    $query_update = "UPDATE users SET username='$username', role='$role', id_unit=$id_unit $sql_pass WHERE id='$id'";
    if (mysqli_query($koneksi, $query_update)) {
        echo "<script>alert('User berhasil diupdate'); window.location='users.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="users.php" class="text-gray-500 hover:text-emerald-600 transition-colors flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h2 class="text-3xl font-bold text-gray-800">Edit User</h2>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <form action="" method="post">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Username</label>
                <input type="text" name="username" value="<?= $row['username'] ?>" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Password <span
                        class="text-sm font-normal text-gray-400">(Biarkan kosong jika tidak diganti)</span></label>
                <input type="password" name="password"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Role</label>
                <select name="role" id="roleSelect"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                    onchange="toggleUnit()">
                    <option value="super_admin" <?= $row['role'] == 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                    <option value="bendahara_putra" <?= $row['role'] == 'bendahara_putra' ? 'selected' : '' ?>>Bendahara
                        Putra</option>
                    <option value="bendahara_putri" <?= $row['role'] == 'bendahara_putri' ? 'selected' : '' ?>>Bendahara
                        Putri</option>
                </select>
            </div>

            <div class="mb-6 <?= $row['role'] == 'super_admin' ? 'hidden' : '' ?>" id="unitContainer">
                <label class="block text-gray-700 font-bold mb-2">Unit</label>
                <select name="id_unit" id="unitSelect"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
                    <option value="1" <?= $row['id_unit'] == 1 ? 'selected' : '' ?>>Putra</option>
                    <option value="2" <?= $row['id_unit'] == 2 ? 'selected' : '' ?>>Putri</option>
                </select>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" name="submit"
                    class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-colors">Simpan
                    Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleUnit() {
        const role = document.getElementById('roleSelect').value;
        const unitContainer = document.getElementById('unitContainer');
        const unitSelect = document.getElementById('unitSelect');

        if (role === 'super_admin') {
            unitContainer.classList.add('hidden');
            unitSelect.value = ''; // Reset
        } else {
            unitContainer.classList.remove('hidden');
            if (role === 'bendahara_putra' && unitSelect.value != '1') unitSelect.value = '1';
            if (role === 'bendahara_putri' && unitSelect.value != '2') unitSelect.value = '2';
        }
    }
</script>

<?php include 'layout/footer.php'; ?>
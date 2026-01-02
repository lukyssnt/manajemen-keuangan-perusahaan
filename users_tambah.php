<?php
include 'layout/header.php';

// Access Control
if ($_SESSION['role'] != 'super_admin') {
    exit;
}

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_raw = $_POST['password'];
    $role = $_POST['role'];
    $id_unit = !empty($_POST['id_unit']) ? $_POST['id_unit'] : 'NULL';

    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    $query_insert = "INSERT INTO users (username, password, role, id_unit) VALUES ('$username', '$password', '$role', $id_unit)";
    if (mysqli_query($koneksi, $query_insert)) {
        echo "<script>alert('User berhasil dibuat'); window.location='users.php';</script>";
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
        <h2 class="text-3xl font-bold text-gray-800">Tambah User</h2>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <form action="" method="post" id="userForm">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Username</label>
                <input type="text" name="username" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" name="password" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Role</label>
                <select name="role" id="roleSelect"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                    onchange="toggleUnit()">
                    <option value="super_admin">Super Admin</option>
                    <option value="bendahara_putra">Bendahara Putra</option>
                    <option value="bendahara_putri">Bendahara Putri</option>
                </select>
            </div>

            <div class="mb-6 hidden" id="unitContainer">
                <label class="block text-gray-700 font-bold mb-2">Unit</label>
                <select name="id_unit" id="unitSelect"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
                    <option value="1">Putra</option>
                    <option value="2">Putri</option>
                </select>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" name="submit"
                    class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-colors">Simpan
                    User</button>
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
            if (role === 'bendahara_putra') unitSelect.value = '1';
            if (role === 'bendahara_putri') unitSelect.value = '2';
        }
    }
</script>

<?php include 'layout/footer.php'; ?>
<?php
require_once 'config/koneksi.php';

$id_user = $_SESSION['id_user'];

// Fetch Current User Data
$query = "SELECT * FROM users WHERE id = '$id_user'";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

if (isset($_POST['submit'])) {
    check_csrf();

    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_baru = $_POST['password_baru'];

    $update_sql = "UPDATE users SET nama = '$nama', username = '$username'";

    // If password is changed
    if (!empty($password_baru)) {
        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
        $update_sql .= ", password = '$hashed_password'";
    }

    $update_sql .= " WHERE id = '$id_user'";

    if (mysqli_query($koneksi, $update_sql)) {
        log_audit("Memperbarui profil (Nama: $nama, Username: $username)");
        header("Location: profil.php?msg=updated");
        exit;
    } else {
        header("Location: profil.php?msg=error");
        exit;
    }
}

include 'layout/header.php';
?>

<div class="max-w-xl mx-auto animate-fade-in-up">
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Edit Profil Saya</h2>
        <p class="text-gray-500">Kelola informasi akun Anda.</p>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 stagger-1">
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nama Lengkap</label>
                <input type="text" name="nama" value="<?= $user['nama'] ?>" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                    placeholder="Masukkan nama lengkap">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Username</label>
                <input type="text" name="username" value="<?= $user['username'] ?>" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                    placeholder="Username login">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Password Baru (Kosongkan jika tidak ganti)</label>
                <input type="password" name="password_baru"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                    placeholder="••••••••">
            </div>

            <div class="flex justify-end">
                <button type="submit" name="submit"
                    class="px-8 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-colors font-bold">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
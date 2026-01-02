<?php
require_once 'config/koneksi.php';

$role = $_SESSION['role'];
$id_unit_session = $_SESSION['id_unit'];

if (isset($_POST['submit'])) {
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);

    // Determine Unit
    if ($role == 'super_admin') {
        $target_unit = $_POST['id_unit'];
    } else {
        $target_unit = $id_unit_session;
    }

    // Insert
    $query_insert = "INSERT INTO santri (id_unit, nis, nama, kelas) VALUES ('$target_unit', '$nis', '$nama', '$kelas')";
    if (mysqli_query($koneksi, $query_insert)) {
        header("Location: santri.php?msg=added");
        exit;
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}

include 'layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="santri.php"
            class="text-gray-500 hover:text-emerald-600 transition-colors flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h2 class="text-3xl font-bold text-gray-800">Tambah Santri</h2>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <form action="" method="post">
            <?php if ($role == 'super_admin'): ?>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Unit</label>
                    <select name="id_unit"
                        class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
                        <option value="1">Putra</option>
                        <option value="2">Putri</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">NIS</label>
                <input type="text" name="nis" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                    placeholder="Nomor Induk Santri">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nama Lengkap</label>
                <input type="text" name="nama" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                    placeholder="Nama Santri">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Kelas</label>
                <input type="text" name="kelas" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                    placeholder="Contoh: 1A, 2B">
            </div>

            <div class="flex justify-end gap-3">
                <button type="reset"
                    class="px-6 py-2 border rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">Reset</button>
                <button type="submit" name="submit"
                    class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
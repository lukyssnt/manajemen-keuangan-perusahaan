<?php
require_once 'config/koneksi.php';

$id = $_GET['id'] ?? '';
$role = $_SESSION['role'];
$id_unit_session = $_SESSION['id_unit'];

// Fetch Data
$query_check = "SELECT * FROM santri WHERE id = '$id'";
$result_check = mysqli_query($koneksi, $query_check);

if (mysqli_num_rows($result_check) == 0) {
    echo "<script>alert('Data tidak ditemukan'); window.location='santri.php';</script>";
    exit;
}

$row = mysqli_fetch_assoc($result_check);

// Security Check
if ($role != 'super_admin' && $row['id_unit'] != $id_unit_session) {
    echo "<script>alert('Akses Ditolak'); window.location='santri.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    // Determine Unit Update
    if ($role == 'super_admin') {
        $target_unit = $_POST['id_unit'];
        $extra_sql = ", id_unit = '$target_unit'";
    } else {
        $extra_sql = "";
    }

    $query_update = "UPDATE santri SET nis='$nis', nama='$nama', kelas='$kelas', status='$status' $extra_sql WHERE id='$id'";
    if (mysqli_query($koneksi, $query_update)) {
        header("Location: santri.php?msg=updated");
        exit;
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}

// Promotion Logic
if (isset($_POST['promote'])) {
    $old_kelas = $row['kelas'];
    $new_kelas = preg_replace_callback('/\d+/', function ($m) {
        return $m[0] + 1;
    }, $old_kelas);

    if ($old_kelas == $new_kelas) {
        echo "<script>alert('Format kelas tidak mengandung angka, silakan ubah manual.');</script>";
    } else {
        mysqli_query($koneksi, "UPDATE santri SET kelas='$new_kelas' WHERE id='$id'");
        header("Location: santri_edit.php?id=$id&msg=promoted");
        exit;
    }
}

// Graduation Logic
if (isset($_POST['graduate'])) {
    mysqli_query($koneksi, "UPDATE santri SET status='Lulus' WHERE id='$id'");
    header("Location: alumni.php?msg=graduated");
    exit;
}

// Selesai logic, baru tampilkan header (HTML)
include 'layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="santri.php"
            class="text-gray-500 hover:text-emerald-600 transition-colors flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h2 class="text-3xl font-bold text-gray-800">Edit Santri</h2>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <form action="" method="post">
            <?php if ($role == 'super_admin'): ?>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Unit</label>
                    <select name="id_unit"
                        class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
                        <option value="1" <?= $row['id_unit'] == 1 ? 'selected' : '' ?>>Putra</option>
                        <option value="2" <?= $row['id_unit'] == 2 ? 'selected' : '' ?>>Putri</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">NIS</label>
                <input type="text" name="nis" value="<?= $row['nis'] ?>" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nama Lengkap</label>
                <input type="text" name="nama" value="<?= $row['nama'] ?>" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Kelas</label>
                <input type="text" name="kelas" value="<?= $row['kelas'] ?>" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Status Santri</label>
                <select name="status"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
                    <option value="Aktif" <?= $row['status'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="Lulus" <?= $row['status'] == 'Lulus' ? 'selected' : '' ?>>Lulus (Alumni)</option>
                </select>
            </div>

            <div class="flex flex-wrap justify-between items-center gap-4 border-t pt-6">
                <div class="flex gap-2">
                    <?php if ($row['status'] == 'Aktif'): ?>
                        <button type="button" onclick="confirmAction('promote')"
                            class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-bold">
                            <i class="fas fa-level-up-alt mr-1"></i> Naik Kelas
                        </button>
                        <button type="button" onclick="confirmAction('graduate')"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-bold">
                            <i class="fas fa-graduation-cap mr-1"></i> Luluskan
                        </button>

                        <!-- Hidden inputs for JS submit -->
                        <input type="submit" name="promote" id="btn-promote" class="hidden">
                        <input type="submit" name="graduate" id="btn-graduate" class="hidden">
                    <?php endif; ?>
                </div>

                <button type="submit" name="update"
                    class="px-8 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-colors font-bold">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmAction(type) {
        let title = (type == 'promote') ? 'Naikkan Kelas?' : 'Luluskan Santri?';
        let text = (type == 'promote') ? 'Santri akan dinaikkan 1 tingkat.' : 'Santri akan dipindahkan ke kategori Alumni.';
        let icon = (type == 'promote') ? 'question' : 'warning';

        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'swal2-emerald-confirm',
                popup: 'swal2-emerald-popup'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('btn-' + type).click();
            }
        });
    }
</script>

<?php include 'layout/footer.php'; ?>
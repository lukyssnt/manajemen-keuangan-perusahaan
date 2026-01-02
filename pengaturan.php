<?php
require_once 'config/koneksi.php';

if ($_SESSION['role'] != 'super_admin') {
    header("Location: index.php");
    exit;
}

// Fetch Current Settings
$query = "SELECT * FROM pengaturan LIMIT 1";
$result = mysqli_query($koneksi, $query);
$settings = mysqli_fetch_assoc($result);

if (isset($_POST['submit'])) {
    check_csrf();

    $nama_aplikasi = mysqli_real_escape_string($koneksi, $_POST['nama_aplikasi']);
    $logo_lama = $settings['logo'];
    $nama_logo_baru = $logo_lama;

    // Handle Logo Upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $nama_logo_baru = "logo_" . time() . "." . $ext;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $nama_logo_baru)) {
            // Delete old logo if exists
            if (!empty($logo_lama) && file_exists($upload_dir . $logo_lama)) {
                @unlink($upload_dir . $logo_lama);
            }
        }
    }

    $update_sql = "UPDATE pengaturan SET nama_aplikasi = '$nama_aplikasi', logo = '$nama_logo_baru' WHERE id = " . $settings['id'];

    if (mysqli_query($koneksi, $update_sql)) {
        log_audit("Memperbarui Pengaturan Website (Aplikasi: $nama_aplikasi)");
        header("Location: pengaturan.php?msg=updated");
        exit;
    } else {
        header("Location: pengaturan.php?msg=error");
        exit;
    }
}

include 'layout/header.php';
?>


        <div class="max-w-2xl mx-auto animate-fade-in-up">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Pengaturan Website</h2>
            <p class="text-gray-500">Sesuaikan identitas aplikasi keungan pesantren.</p>
        </div>
        <div class="flex gap-3 items-center">
            <a href="database_backup.php" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-all shadow-md flex items-center gap-2 text-sm font-bold">
               <i class="fas fa-database"></i> Backup DB
            </a>
            <?php if (!empty($settings['logo'])): ?>
                <img src="uploads/<?= $settings['logo'] ?>" alt="Logo"
                    class="h-16 w-16 object-contain rounded border bg-gray-50 p-1">
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 stagger-1">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Nama Website / Pesantren</label>
                <input type="text" name="nama_aplikasi" value="<?= $settings['nama_aplikasi'] ?>" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                    placeholder="Contoh: SIKEP MAARIF">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Logo Website</label>
                <input type="file" name="logo" accept="image/*"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500 text-sm">
                <p class="text-xs text-gray-500 mt-2 italic">*Kosongkan jika tidak ingin mengubah logo.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" name="submit"
                    class="px-10 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-all font-bold">
                    Update Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
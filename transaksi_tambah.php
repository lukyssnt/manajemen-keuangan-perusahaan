<?php
require_once 'config/koneksi.php';

$role = $_SESSION['role'];
$id_unit_session = $_SESSION['id_unit'];
$id_user = $_SESSION['id_user'];

if (isset($_POST['submit'])) {
    check_csrf();

    $jenis = $_POST['jenis'];
    $nominal = (int) str_replace(['.', ','], '', $_POST['nominal']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // Determine Unit
    if ($role == 'super_admin') {
        $target_unit = $_POST['id_unit'];
    } else {
        $target_unit = $id_unit_session;
    }

    // Validasi Saldo untuk Pengeluaran
    if ($jenis == 'Keluar') {
        $q_cek = mysqli_query($koneksi, "SELECT saldo FROM rekening WHERE id_unit = '$target_unit'");
        $r_cek = mysqli_fetch_assoc($q_cek);
        $current_saldo = $r_cek['saldo'];

        if ($current_saldo < $nominal) {
            header("Location: transaksi_tambah.php?msg=error_saldo");
            exit;
        }
    }

    $nama_file_baru = null;

    // Handle File Upload Nota
    if (isset($_FILES['nota']) && $_FILES['nota']['error'] == 0) {
        $nama_file = $_FILES['nota']['name'];
        $tmp_file = $_FILES['nota']['tmp_name'];
        $ext = pathinfo($nama_file, PATHINFO_EXTENSION);
        $nama_file_baru = "NOTA_" . time() . "_" . uniqid() . "." . $ext;

        $upload_dir = 'uploads/nota/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        move_uploaded_file($tmp_file, $upload_dir . $nama_file_baru);
    }

    // 1. Insert Transaksi
    $query_insert = "INSERT INTO transaksi (id_unit, id_user, jenis, nominal, keterangan, nota) 
                    VALUES ('$target_unit', '$id_user', '$jenis', '$nominal', '$keterangan', " .
        ($nama_file_baru ? "'$nama_file_baru'" : "NULL") . ")";

    if (mysqli_query($koneksi, $query_insert)) {
        // 2. Update Saldo Rekening
        if ($jenis == 'Masuk') {
            $q_update = "UPDATE rekening SET saldo = saldo + $nominal WHERE id_unit = '$target_unit'";
        } else {
            $q_update = "UPDATE rekening SET saldo = saldo - $nominal WHERE id_unit = '$target_unit'";
        }
        mysqli_query($koneksi, $q_update);

        log_audit("Menambah transaksi $jenis senilai " . format_rupiah($nominal) . ": $keterangan");

        header("Location: transaksi.php?msg=added");
        exit;
    } else {
        header("Location: transaksi.php?msg=error");
        exit;
    }
}

include 'layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="transaksi.php"
            class="text-gray-500 hover:text-emerald-600 transition-colors flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h2 class="text-3xl font-bold text-gray-800">Tambah Transaksi</h2>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Jenis Transaksi</label>
                    <select name="jenis"
                        class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
                        <option value="Masuk">Pemasukan (+)</option>
                        <option value="Keluar">Pengeluaran (-)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Nominal (Rp)</label>
                    <input type="number" name="nominal" min="0" required
                        class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                        placeholder="0">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Keterangan</label>
                <textarea name="keterangan" rows="2" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500"
                    placeholder="Contoh: Pembayaran SPP Santri X, Belanja Dapur..."></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Lampiran Nota/Foto (Opsional)</label>
                <input type="file" name="nota" accept="image/*"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500 text-sm">
                <p class="text-xs text-gray-500 mt-1 italic">*Format: JPG, PNG, GIF</p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="reset"
                    class="px-6 py-2 border rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">Reset</button>
                <button type="submit" name="submit"
                    class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-colors">Simpan
                    Transaksi</button>
            </div>
        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
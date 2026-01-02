<?php
require_once 'config/koneksi.php';

$id_tagihan = $_GET['id'] ?? null;
if (!$id_tagihan) {
    header("Location: tagihan.php");
    exit;
}

// Fetch Tagihan Info
$q = mysqli_query($koneksi, "SELECT t.*, s.nama, s.kelas FROM tagihan t JOIN santri s ON t.id_santri = s.id WHERE t.id = '$id_tagihan'");
$row = mysqli_fetch_assoc($q);

if (!$row) {
    header("Location: tagihan.php");
    exit;
}

if ($row['status'] == 'Lunas') {
    header("Location: tagihan.php?msg=already_paid");
    exit;
}

$sisa_tagihan = $row['nominal'] - $row['terbayar'];

if (isset($_POST['bayar'])) {
    check_csrf();

    $jumlah = (int) $_POST['jumlah'];
    $keterangan_tambahan = $_POST['keterangan'];
    $keterangan_trx = "Pembayaran " . $row['judul'] . " - " . $row['nama'] . ($keterangan_tambahan ? " ($keterangan_tambahan)" : "");
    $id_user = $_SESSION['id_user'];
    $id_unit = $row['id_unit'];

    // 1. Insert ke Transaksi Kas (Masuk)
    $sql_trx = "INSERT INTO transaksi (id_unit, id_user, jenis, nominal, keterangan) VALUES ('$id_unit', '$id_user', 'Masuk', '$jumlah', '$keterangan_trx')";
    if (mysqli_query($koneksi, $sql_trx)) {
        $id_trx = mysqli_insert_id($koneksi);

        // 2. Insert ke Pembayaran Tagihan
        $sql_bayar = "INSERT INTO pembayaran_tagihan (id_tagihan, jumlah, keterangan, id_transaksi) VALUES ('$id_tagihan', '$jumlah', '$keterangan_tambahan', '$id_trx')";
        mysqli_query($koneksi, $sql_bayar);

        // 3. Update Rekening Unit
        mysqli_query($koneksi, "UPDATE rekening SET saldo = saldo + $jumlah WHERE id_unit = '$id_unit'");

        // 4. Update Status Tagihan
        $terbayar_baru = $row['terbayar'] + $jumlah;
        if ($terbayar_baru >= $row['nominal']) {
            $status_baru = 'Lunas';
        } elseif ($terbayar_baru > 0) {
            $status_baru = 'Sebagian';
        } else {
            $status_baru = 'Belum Lunas';
        }

        $sql_upd_tagihan = "UPDATE tagihan SET terbayar = '$terbayar_baru', status = '$status_baru' WHERE id = '$id_tagihan'";
        mysqli_query($koneksi, $sql_upd_tagihan);

        log_audit("Melakukan pembayaran tagihan ID $id_tagihan senilai " . format_rupiah($jumlah));

        header("Location: tagihan_detail.php?id_santri=" . $row['id_santri'] . "&msg=paid");
        exit;
    }
}

include 'layout/header.php';
?>

<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="tagihan.php"
            class="text-gray-500 hover:text-emerald-600 transition-colors flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h2 class="text-3xl font-bold text-gray-800">Bayar Tagihan</h2>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <div class="mb-6 border-b pb-4">
            <p class="text-sm text-gray-500">Rincian Santri</p>
            <h3 class="text-lg font-bold">
                <?= $row['nama'] ?> <span class="text-sm font-normal text-gray-600">(
                    <?= $row['kelas'] ?>)
                </span>
            </h3>
        </div>

        <div class="mb-6 border-b pb-4">
            <p class="text-sm text-gray-500">Identitas Tagihan</p>
            <h3 class="text-xl font-bold text-emerald-700">
                <?= $row['judul'] ?>
            </h3>
            <div class="flex justify-between mt-2">
                <span>Total Tagihan:</span>
                <span class="font-bold">Rp
                    <?= number_format($row['nominal'], 0, ',', '.') ?>
                </span>
            </div>
            <div class="flex justify-between mt-1 text-gray-500">
                <span>Sudah Dibayar:</span>
                <span>Rp
                    <?= number_format($row['terbayar'], 0, ',', '.') ?>
                </span>
            </div>
            <div class="flex justify-between mt-2 p-2 bg-red-50 text-red-700 rounded-lg">
                <span class="font-bold">Sisa Pembayaran:</span>
                <span class="font-bold">Rp
                    <?= number_format($sisa_tagihan, 0, ',', '.') ?>
                </span>
            </div>
        </div>

        <form action="" method="post">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Jumlah Pembayaran (Rp)</label>
                <input type="number" name="jumlah" value="<?= $sisa_tagihan ?>" max="<?= $sisa_tagihan ?>" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500 text-lg font-bold text-gray-800">
                <p class="text-xs text-gray-500 mt-1">Maksimal Rp
                    <?= number_format($sisa_tagihan, 0, ',', '.') ?>
                </p>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Catatan (Opsional)</label>
                <input type="text" name="keterangan" placeholder="Contoh: Titipan Orang Tua, via Transfer..."
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" name="bayar"
                    class="w-full px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-colors font-bold text-lg">
                    <i class="fas fa-wallet mr-2"></i> Proses Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
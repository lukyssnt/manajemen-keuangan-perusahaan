<?php
require_once 'config/koneksi.php';

$role = $_SESSION['role'];
$id_unit_session = $_SESSION['id_unit'];

if (isset($_POST['import'])) {
    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);

    if ($fileExt == 'csv') {
        $file = fopen($fileTmp, "r");
        $count = 0;
        $status = 'Aktif';

        $first_line = fgets($file);
        if (trim($first_line) !== 'sep=;' && trim($first_line) !== 'sep=,') {
            rewind($file);
        }

        $delimiter = ";";
        while (($data = fgetcsv($file, 1000, $delimiter)) !== FALSE) {
            if (count($data) == 1 && strpos($data[0], ',') !== false) {
                rewind($file);
                $first_line = fgets($file);
                if (trim($first_line) !== 'sep=;' && trim($first_line) !== 'sep=,') {
                    rewind($file);
                }
                $delimiter = ",";
                continue;
            }

            if (strtolower($data[0] ?? '') == 'nis')
                continue;

            $nis = mysqli_real_escape_string($koneksi, $data[0]);
            $nama = mysqli_real_escape_string($koneksi, $data[1]);
            $kelas = mysqli_real_escape_string($koneksi, $data[2]);
            $status_raw = isset($data[3]) ? mysqli_real_escape_string($koneksi, $data[3]) : 'Aktif';

            if (strtolower($status_raw) == 'lulus' || strtolower($status_raw) == 'alumni') {
                $status = 'Lulus';
            } else {
                $status = 'Aktif';
            }

            if ($role == 'super_admin') {
                $target_unit = $_POST['id_unit'];
            } else {
                $target_unit = $id_unit_session;
            }

            if (!empty($nis) && !empty($nama)) {
                $query = "INSERT INTO santri (id_unit, nis, nama, kelas, status) VALUES ('$target_unit', '$nis', '$nama', '$kelas', '$status') 
                          ON DUPLICATE KEY UPDATE nama='$nama', kelas='$kelas', status='$status'";
                mysqli_query($koneksi, $query);
                $count++;
            }
        }
        fclose($file);
        $redirect = ($status == 'Lulus') ? 'alumni.php' : 'santri.php';
        header("Location: $redirect?msg=added");
        exit;
    }
}

include 'layout/header.php';
?>

<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="santri.php"
            class="text-gray-500 hover:text-emerald-600 transition-colors flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h2 class="text-3xl font-bold text-gray-800">Import Santri</h2>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <div class="mb-6 bg-blue-50 text-blue-800 p-4 rounded-lg text-sm">
            <strong>Format CSV:</strong><br>
            Kolom 1: NIS<br>
            Kolom 2: Nama Lengkap<br>
            Kolom 3: Kelas<br>
            Kolom 4: Status (Aktif / Lulus - Opsional, default: Aktif)<br>
            (Tanpa header atau Header baris pertama akan dilewati jika berisi 'NIS')
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <?php if ($role == 'super_admin'): ?>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Target Unit</label>
                    <select name="id_unit"
                        class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
                        <option value="1">Putra</option>
                        <option value="2">Putri</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">File CSV</label>
                <input type="file" name="file" accept=".csv" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="flex justify-between items-center">
                <a href="santri_template.php"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                    <i class="fas fa-download"></i> Download Template CSV
                </a>
                <div class="flex gap-2">
                    <button type="submit" name="import"
                        class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-colors font-bold">
                        Import Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
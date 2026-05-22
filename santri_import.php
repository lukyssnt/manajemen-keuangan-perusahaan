<?php
require_once 'config/koneksi.php';

$role = $_SESSION['role'];
$id_unit_session = $_SESSION['id_unit'];

if (isset($_POST['sync'])) {
    $dbAbsen = new DatabaseAbsen();
    $connAbsen = $dbAbsen->getConnection();

    if ($connAbsen) {
        try {
            // Filter by jenis_kelamin based on unit for non-super_admin
            // Unit 1 (Putra) = L, Unit 2 (Putri) = P
            $filter = "";
            if ($role != 'super_admin') {
                if ($id_unit_session == 1) {
                    $filter = "WHERE jenis_kelamin = 'L'";
                } else if ($id_unit_session == 2) {
                    $filter = "WHERE jenis_kelamin = 'P'";
                }
            }

            // Query ke database absen
            $query_santri = "SELECT nis, nama, kelas_id, status, jenis_kelamin FROM mst_santri $filter";
            $stmt = $connAbsen->prepare($query_santri);
            $stmt->execute();
            $data_santri = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $count = 0;
            
            // Gunakan Transaction untuk optimasi bulk insert
            mysqli_begin_transaction($koneksi);
            
            try {
                foreach ($data_santri as $row) {
                    $nis = mysqli_real_escape_string($koneksi, $row['nis']);
                    $nama = mysqli_real_escape_string($koneksi, $row['nama']);
                    
                    // Untuk sementara kita pakai kelas_id jika tabel kelas di db absen tidak dipetakan
                    $kelas = mysqli_real_escape_string($koneksi, $row['kelas_id'] ?? '-'); 
                    
                    $status_raw = $row['status'];
                    $jk = $row['jenis_kelamin'];

                    if ($status_raw == 'Alumni' || $status_raw == 'Keluar') {
                        $status = 'Lulus';
                    } else {
                        $status = 'Aktif';
                    }
                    
                    // Tentukan unit otomatis dari jenis kelamin jika Super Admin
                    if ($role == 'super_admin') {
                        $target_unit = ($jk == 'P') ? 2 : 1; 
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
                
                // Commit jika semua query berhasil
                mysqli_commit($koneksi);
                
                header("Location: santri.php?msg=synced&count=$count");
                exit;
            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $error_msg = "Gagal sinkronisasi data lokal: " . $e->getMessage();
            }
        } catch (PDOException $e) {
            $error_msg = "Gagal terhubung/query ke database absen: " . $e->getMessage();
        }
    } else {
        $error_msg = "Koneksi database absen gagal. Pastikan detail database benar.";
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
        <h2 class="text-3xl font-bold text-gray-800">Sinkronisasi Santri</h2>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 shadow-sm" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($error_msg) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <div class="mb-6 bg-blue-50 text-blue-800 p-5 rounded-lg text-sm border border-blue-100">
            <strong class="text-lg block mb-2"><i class="fas fa-info-circle mr-1"></i> Informasi Sinkronisasi</strong>
            Data santri akan ditarik secara otomatis dari database sistem absensi. Aturan integrasi:
            <ul class="list-disc ml-5 mt-2 space-y-1">
                <li>Santri laki-laki (L) otomatis masuk ke unit <b>Putra</b>.</li>
                <li>Santri perempuan (P) otomatis masuk ke unit <b>Putri</b>.</li>
                <li>Santri dengan status 'Alumni' atau 'Keluar' akan diset sebagai <b>'Lulus'</b>.</li>
                <li>Jika data NIS sudah ada, nama dan statusnya akan <b>diperbarui (Update)</b>.</li>
            </ul>
        </div>

        <form action="" method="post">
            <div class="flex justify-end items-center">
                <button type="submit" name="sync"
                    class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-all font-bold flex items-center gap-2 hover:-translate-y-0.5">
                    <i class="fas fa-sync-alt"></i> Tarik Data Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
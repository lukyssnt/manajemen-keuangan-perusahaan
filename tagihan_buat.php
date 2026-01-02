<?php
require_once 'config/koneksi.php';

$role = $_SESSION['role'];
$id_unit_session = $_SESSION['id_unit'];

if (isset($_POST['submit'])) {
    check_csrf();

    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $nominal = (int) str_replace(['.', ','], '', $_POST['nominal']);
    $target_type = $_POST['target_type'];

    if ($role == 'super_admin') {
        $target_unit = $_POST['id_unit'];
    } else {
        $target_unit = $id_unit_session;
    }

    $santri_ids = [];
    if ($target_type == 'all') {
        $q = mysqli_query($koneksi, "SELECT id FROM santri WHERE id_unit = '$target_unit'");
        while ($r = mysqli_fetch_assoc($q)) {
            $santri_ids[] = $r['id'];
        }
    } elseif ($target_type == 'kelas') {
        $target_kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
        $q = mysqli_query($koneksi, "SELECT id FROM santri WHERE id_unit = '$target_unit' AND kelas = '$target_kelas'");
        while ($r = mysqli_fetch_assoc($q)) {
            $santri_ids[] = $r['id'];
        }
    } elseif ($target_type == 'individual') {
        if (isset($_POST['id_santri']) && is_array($_POST['id_santri'])) {
            $santri_ids = $_POST['id_santri'];
        }
    }

    $count = 0;
    foreach ($santri_ids as $sid) {
        $sid = mysqli_real_escape_string($koneksi, $sid);
        $sql = "INSERT INTO tagihan (id_unit, id_santri, judul, nominal) 
                VALUES ('$target_unit', '$sid', '$judul', '$nominal')";

        if (mysqli_query($koneksi, $sql)) {
            $count++;
        }
    }

    log_audit("Membuat tagihan massal: $judul senilai " . format_rupiah($nominal) . " untuk $count santri.");

    header("Location: tagihan.php?msg=added");
    exit;
}

include 'layout/header.php';

// Fetch Classes for Dropdown
$unit_filter_kls = ($role != 'super_admin') ? "WHERE id_unit = '$id_unit_session'" : "";
$q_kelas = mysqli_query($koneksi, "SELECT DISTINCT kelas FROM santri $unit_filter_kls ORDER BY kelas");

// Fetch Santri for Individual
if ($role != 'super_admin') {
    $q_santri = mysqli_query($koneksi, "SELECT id, nama, kelas FROM santri WHERE id_unit = '$id_unit_session' ORDER BY nama");
} else {
    $q_santri = mysqli_query($koneksi, "SELECT id, nama, kelas, id_unit FROM santri ORDER BY nama");
}
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="tagihan.php"
            class="text-gray-500 hover:text-emerald-600 transition-colors flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h2 class="text-3xl font-bold text-gray-800">Buat Tagihan Baru</h2>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <form action="" method="post">

            <?php if ($role == 'super_admin'): ?>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Unit Target</label>
                    <select name="id_unit" required
                        class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
                        <option value="1">Putra</option>
                        <option value="2">Putri</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih unit untuk memastikan santri yang ditagih sesuai.</p>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nama Tagihan</label>
                <input type="text" name="judul" required placeholder="Contoh: SPP Januari 2024"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nominal (Rp)</label>
                <input type="number" name="nominal" required
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Target Tagihan</label>
                <select name="target_type" id="targetType" onchange="toggleTarget()"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500 mb-2">
                    <option value="all">Semua Santri (Per Unit)</option>
                    <option value="kelas">Per Kelas</option>
                    <option value="individual">Perorangan (Satu Santri)</option>
                </select>

                <!-- Kelas Select -->
                <div id="kelasSelect" class="hidden mt-2 p-4 bg-gray-50 rounded-lg">
                    <label class="block text-sm text-gray-600 mb-1">Pilih Kelas</label>
                    <select name="kelas" class="w-full border rounded-lg px-3 py-2">
                        <?php while ($r = mysqli_fetch_assoc($q_kelas)): ?>
                            <option value="<?= $r['kelas'] ?>">
                                <?= $r['kelas'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Individual Select -->
                <div id="individuSelect" class="hidden mt-2 p-4 bg-gray-50 rounded-lg">
                    <label class="block text-sm text-gray-600 mb-2 font-bold">Pilih Santri (Bisa pilih banyak)</label>
                    <div class="mb-2 relative">
                        <input type="text" id="searchSantri" onkeyup="filterSantri()" placeholder="Cari santri..."
                            class="text-sm w-full border rounded-lg pl-8 pr-3 py-1 focus:outline-none focus:border-emerald-500">
                        <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                    </div>
                    <div class="mb-2 flex items-center gap-2">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                        <label for="selectAll" class="text-xs text-gray-500 cursor-pointer">Pilih Semua</label>
                    </div>
                    <div class="max-h-60 overflow-y-auto bg-white border rounded-lg p-2" id="santriList">
                        <?php while ($r = mysqli_fetch_assoc($q_santri)): ?>
                            <div
                                class="flex items-center gap-3 p-2 hover:bg-emerald-50 rounded transition-colors santri-item">
                                <input type="checkbox" name="id_santri[]" value="<?= $r['id'] ?>"
                                    class="santri-check rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-800 santri-name">
                                        <?= $row['nama_unit'] ?? '' ?>     <?= $r['nama'] ?>
                                    </div>
                                    <div class="text-[10px] text-gray-500"><?= $r['kelas'] ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" name="submit"
                    class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-colors">Buat
                    Tagihan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleTarget() {
        const type = document.getElementById('targetType').value;
        document.getElementById('kelasSelect').classList.add('hidden');
        document.getElementById('individuSelect').classList.add('hidden');

        if (type === 'kelas') {
            document.getElementById('kelasSelect').classList.remove('hidden');
        } else if (type === 'individual') {
            document.getElementById('individuSelect').classList.remove('hidden');
        }
    }

    function filterSantri() {
        const input = document.getElementById('searchSantri');
        const filter = input.value.toLowerCase();
        const items = document.querySelectorAll('.santri-item');

        items.forEach(item => {
            const name = item.querySelector('.santri-name').innerText.toLowerCase();
            if (name.indexOf(filter) > -1) {
                item.style.display = "";
            } else {
                item.style.display = "none";
            }
        });
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.santri-check');

        checkboxes.forEach(cb => {
            if (cb.parentElement.style.display !== 'none') {
                cb.checked = selectAll.checked;
            }
        });
    }
</script>

<?php include 'layout/footer.php'; ?>
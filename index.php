<?php
include 'layout/header.php';

// Logic to fetch Dashboard Data
$id_unit = $_SESSION['id_unit'];
$role = $_SESSION['role'];

// Filter Query Logic
$unit_filter = "";
if ($role != 'super_admin') {
    $unit_filter = "WHERE id_unit = '$id_unit'";
}

// 1. Total Saldo
// Untuk Super Admin, hitung total semua rekening. Untuk Bendahara, hanya unitnya.
$query_saldo = "SELECT SUM(saldo) as total_saldo FROM rekening " . $unit_filter;
$result_saldo = mysqli_query($koneksi, $query_saldo);
$row_saldo = mysqli_fetch_assoc($result_saldo);
$total_saldo = $row_saldo['total_saldo'] ?? 0;

// 2. Pemasukan & Pengeluaran (Bulan Ini)
$month = date('m');
$year = date('Y');

$unit_filter_transaksi = "";
if ($role != 'super_admin') {
    $unit_filter_transaksi = "AND id_unit = '$id_unit'";
}

// Pemasukan
$query_masuk = "SELECT SUM(nominal) as total_masuk FROM transaksi WHERE jenis = 'Masuk' AND MONTH(tanggal) = '$month' AND YEAR(tanggal) = '$year' $unit_filter_transaksi";
$result_masuk = mysqli_query($koneksi, $query_masuk);
$row_masuk = mysqli_fetch_assoc($result_masuk);
$total_masuk = $row_masuk['total_masuk'] ?? 0;

// Pengeluaran
$query_keluar = "SELECT SUM(nominal) as total_keluar FROM transaksi WHERE jenis = 'Keluar' AND MONTH(tanggal) = '$month' AND YEAR(tanggal) = '$year' $unit_filter_transaksi";
$result_keluar = mysqli_query($koneksi, $query_keluar);
$row_keluar = mysqli_fetch_assoc($result_keluar);
$total_keluar = $row_keluar['total_keluar'] ?? 0;

// 3. Transaksi Terbaru (5 Terakhir)
$limit_unit = ($role != 'super_admin') ? "WHERE id_unit = '$id_unit'" : "";
$query_recent = "SELECT t.*, u.nama_unit 
                 FROM transaksi t 
                 JOIN units u ON t.id_unit = u.id 
                 $limit_unit 
                 ORDER BY t.tanggal DESC LIMIT 5";
$result_recent = mysqli_query($koneksi, $query_recent);

?>

<div class="mb-8 flex justify-between items-center animate-fade-in-up">
    <div>
        <h2 class="text-3xl font-bold text-gray-800">Selamat Datang, <?= $display_name ?>!</h2>
        <p class="text-gray-500">Ringkasan keuangan <?= $sys['nama_aplikasi'] ?: 'pesantren' ?> hari ini.</p>
    </div>

    <!-- Date/Period Display (Optional) -->
    <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100 text-sm font-medium text-gray-600">
        <i class="far fa-calendar-alt mr-2"></i>
        <?= date('F Y') ?>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card 1: Total Saldo -->
    <div
        class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl p-6 text-white shadow-lg relative overflow-hidden animate-fade-in-up stagger-1 hover-card">
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2">
            <i class="fas fa-wallet text-9xl"></i>
        </div>
        <div class="relative z-10">
            <p class="text-emerald-100 font-medium mb-1">Total Saldo Kas</p>
            <h3 class="text-3xl font-bold">Rp
                <?= number_format($total_saldo, 0, ',', '.') ?>
            </h3>
            <p class="text-xs text-emerald-100 mt-2 opacity-80">
                <?php if ($role == 'super_admin')
                    echo "Gabungan Putra & Putri";
                else
                    echo "Unit " . ucfirst($role == 'bendahara_putra' ? 'Putra' : 'Putri'); ?>
            </p>
        </div>
    </div>

    <!-- Card 2: Pemasukan Bulan Ini -->
    <div
        class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex items-center justify-between animate-fade-in-up stagger-2 hover-card">
        <div>
            <p class="text-gray-500 font-medium text-sm mb-1">Pemasukan (Bulan Ini)</p>
            <h3 class="text-2xl font-bold text-gray-800">Rp
                <?= number_format($total_masuk, 0, ',', '.') ?>
            </h3>
            <p class="text-xs text-emerald-500 mt-1 font-medium">
                <i class="fas fa-arrow-up"></i> Income
            </p>
        </div>
        <div class="h-12 w-12 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
            <i class="fas fa-arrow-down transform rotate-180"></i>
        </div>
    </div>

    <!-- Card 3: Pengeluaran Bulan Ini -->
    <div
        class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex items-center justify-between animate-fade-in-up stagger-3 hover-card">
        <div>
            <p class="text-gray-500 font-medium text-sm mb-1">Pengeluaran (Bulan Ini)</p>
            <h3 class="text-2xl font-bold text-gray-800">Rp
                <?= number_format($total_keluar, 0, ',', '.') ?>
            </h3>
            <p class="text-xs text-red-500 mt-1 font-medium">
                <i class="fas fa-arrow-down"></i> Expense
            </p>
        </div>
        <div class="h-12 w-12 bg-red-100 rounded-full flex items-center justify-center text-red-600">
            <i class="fas fa-arrow-up transform rotate-45"></i>
        </div>
    </div>
</div>

<!-- Charts & Recent Transactions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Chart Section (Simplified or Placeholder) -->
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100 animate-fade-in-up stagger-4">
        <h3 class="font-bold text-lg text-gray-800 mb-4">Grafik Keuangan</h3>
        <div class="relative h-64 w-full">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 animate-fade-in-up stagger-4">
        <h3 class="font-bold text-lg text-gray-800 mb-4">Transaksi Terbaru</h3>
        <div class="space-y-4">
            <?php if (mysqli_num_rows($result_recent) > 0) {
                while ($row = mysqli_fetch_assoc($result_recent)) {
                    $is_masuk = $row['jenis'] == 'Masuk';
                    $icon = $is_masuk ? 'fa-arrow-down text-emerald-600' : 'fa-arrow-up text-red-600';
                    $bg = $is_masuk ? 'bg-emerald-100' : 'bg-red-100';
                    $color = $is_masuk ? 'text-emerald-600' : 'text-red-600';
                    $sign = $is_masuk ? '+' : '-';
                    ?>
                    <div
                        class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 <?= $bg ?> rounded-full flex items-center justify-center">
                                <i class="fas <?= $icon ?>"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800 line-clamp-1">
                                    <?= $row['keterangan'] ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold <?= $color ?>">
                                <?= $sign ?> Rp
                                <?= number_format($row['nominal'], 0, ',', '.') ?>
                            </p>
                            <?php if ($role == 'super_admin'): ?>
                                <span class="text-[10px] uppercase px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">
                                    <?= $row['nama_unit'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php }
            } else { ?>
                <div class="text-center text-gray-400 py-8">Belum ada transaksi</div>
            <?php } ?>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100 text-center">
            <a href="transaksi.php" class="text-sm text-emerald-600 font-medium hover:text-emerald-700">Lihat Semua
                Transaksi &rarr;</a>
        </div>
    </div>
</div>

<!-- Chart Script -->
<?php
// Fetch Chart Data (Last 6 Months)
$chart_labels = [];
$chart_masuk = [];
$chart_keluar = [];

for ($i = 5; $i >= 0; $i--) {
    $m = date('m', strtotime("-$i months"));
    $y = date('Y', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $chart_labels[] = $label;

    $q_m = mysqli_query($koneksi, "SELECT SUM(nominal) as total FROM transaksi WHERE jenis='Masuk' AND MONTH(tanggal)='$m' AND YEAR(tanggal)='$y' $unit_filter_transaksi");
    $r_m = mysqli_fetch_assoc($q_m);
    $chart_masuk[] = (int) ($r_m['total'] ?? 0);

    $q_k = mysqli_query($koneksi, "SELECT SUM(nominal) as total FROM transaksi WHERE jenis='Keluar' AND MONTH(tanggal)='$m' AND YEAR(tanggal)='$y' $unit_filter_transaksi");
    $r_k = mysqli_fetch_assoc($q_k);
    $chart_keluar[] = (int) ($r_k['total'] ?? 0);
}
?>
<script>
    const ctx = document.getElementById('financeChart').getContext('2d');
    const chartData = {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [
            {
                label: 'Pemasukan',
                data: <?= json_encode($chart_masuk) ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Pengeluaran',
                data: <?= json_encode($chart_keluar) ?>,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.05)',
                tension: 0.4,
                fill: true
            }
        ]
    };

    const config = {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [2, 4] },
                    ticks: {
                        callback: function (value) {
                            return 'Rp ' + value.toLocaleString();
                        }
                    }
                },
                x: { grid: { display: false } }
            }
        }
    };

    new Chart(ctx, config);
</script>

<?php include 'layout/footer.php'; ?>
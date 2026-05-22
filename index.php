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

<div class="mb-8 animate-fade-in-up">
    <div class="bg-white/95 rounded-[28px] border border-white/80 shadow-soft overflow-hidden relative">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.18),transparent_28%),radial-gradient(circle_at_left,rgba(45,212,191,0.12),transparent_24%)] pointer-events-none"></div>
        <div class="relative p-6 md:p-8 lg:p-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold uppercase tracking-[0.22em] border border-emerald-100 mb-5">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Financial Overview
                </div>
                <h2 class="font-display text-3xl md:text-4xl text-slate-900 leading-tight">Selamat Datang, <?= $display_name ?>!</h2>
                <p class="text-slate-500 mt-3 text-sm md:text-base leading-7">Ringkasan keuangan <?= $sys['nama_aplikasi'] ?: 'pesantren' ?> disajikan dalam tampilan yang lebih lega, bersih, dan nyaman dipakai setiap hari.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <div class="ghost-button px-4 py-3 text-sm font-medium inline-flex items-center gap-2">
                        <i class="far fa-calendar-alt text-emerald-600"></i>
                        <?= date('F Y') ?>
                    </div>
                    <div class="ghost-button px-4 py-3 text-sm font-medium inline-flex items-center gap-2">
                        <i class="fas fa-shield-heart text-teal-600"></i>
                        Monitoring kas realtime
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 min-w-full sm:min-w-[340px] lg:min-w-[380px] lg:max-w-[420px]">
                <div class="rounded-[24px] bg-slate-900 text-white p-5 shadow-panel">
                    <div class="text-xs uppercase tracking-[0.2em] text-white/60">Saldo Saat Ini</div>
                    <div class="mt-3 text-2xl font-display font-semibold">Rp <?= number_format($total_saldo, 0, ',', '.') ?></div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Stabil dan terpantau
                    </div>
                </div>
                <div class="rounded-[24px] bg-gradient-to-br from-emerald-500 to-teal-500 text-white p-5 shadow-emerald">
                    <div class="text-xs uppercase tracking-[0.2em] text-white/70">Periode</div>
                    <div class="mt-3 text-2xl font-display font-semibold"><?= date('M Y') ?></div>
                    <div class="mt-4 text-xs text-white/75">Pemantauan transaksi bulanan</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card 1: Total Saldo -->
    <div
        class="bg-gradient-to-br from-emerald-500 via-emerald-500 to-teal-500 rounded-[28px] p-7 text-white shadow-emerald relative overflow-hidden hover-card border border-white/10">
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-3 -translate-y-3">
            <i class="fas fa-wallet text-[7rem]"></i>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/10 to-transparent"></div>
        <div class="relative z-10">
            <div class="h-12 w-12 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center mb-5">
                <i class="fas fa-sack-dollar text-lg"></i>
            </div>
            <p class="text-emerald-50/85 font-medium mb-2 text-sm uppercase tracking-[0.2em]">Total Saldo Kas</p>
            <h3 class="font-display text-3xl md:text-[2rem] font-semibold">Rp
                <?= number_format($total_saldo, 0, ',', '.') ?>
            </h3>
            <p class="text-sm text-emerald-50/85 mt-3 leading-6">
                <?php if ($role == 'super_admin')
                    echo "Gabungan Putra & Putri";
                else
                    echo "Unit " . ucfirst($role == 'bendahara_putra' ? 'Putra' : 'Putri'); ?>
            </p>
        </div>
    </div>

    <!-- Card 2: Pemasukan Bulan Ini -->
    <div
        class="bg-white rounded-[28px] p-7 shadow-soft border border-white/80 flex items-start justify-between hover-card">
        <div class="pr-4">
            <div class="h-12 w-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-5 shadow-inner">
                <i class="fas fa-arrow-down-long rotate-180 text-lg"></i>
            </div>
            <p class="text-slate-500 font-medium text-sm mb-2 uppercase tracking-[0.18em]">Pemasukan</p>
            <h3 class="font-display text-2xl md:text-[1.8rem] font-semibold text-slate-900">Rp
                <?= number_format($total_masuk, 0, ',', '.') ?>
            </h3>
            <p class="text-sm text-emerald-600 mt-3 font-medium">
                <i class="fas fa-arrow-trend-up mr-1"></i> Income bulan ini
            </p>
        </div>
        <div class="hidden sm:block text-right">
            <div class="text-xs uppercase tracking-[0.18em] text-slate-400 mb-2">Status</div>
            <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-semibold">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Positif
            </div>
        </div>
    </div>

    <!-- Card 3: Pengeluaran Bulan Ini -->
    <div
        class="bg-white rounded-[28px] p-7 shadow-soft border border-white/80 flex items-start justify-between hover-card">
        <div class="pr-4">
            <div class="h-12 w-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 mb-5 shadow-inner">
                <i class="fas fa-arrow-up-long rotate-45 text-lg"></i>
            </div>
            <p class="text-slate-500 font-medium text-sm mb-2 uppercase tracking-[0.18em]">Pengeluaran</p>
            <h3 class="font-display text-2xl md:text-[1.8rem] font-semibold text-slate-900">Rp
                <?= number_format($total_keluar, 0, ',', '.') ?>
            </h3>
            <p class="text-sm text-rose-600 mt-3 font-medium">
                <i class="fas fa-arrow-trend-down mr-1"></i> Expense bulan ini
            </p>
        </div>
        <div class="hidden sm:block text-right">
            <div class="text-xs uppercase tracking-[0.18em] text-slate-400 mb-2">Status</div>
            <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-rose-50 text-rose-700 border border-rose-100 text-xs font-semibold">
                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                Terkendali
            </div>
        </div>
    </div>
</div>

<!-- Charts & Recent Transactions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Chart Section (Simplified or Placeholder) -->
    <div class="lg:col-span-2 bg-white p-6 md:p-8 rounded-[28px] shadow-soft border border-white/80">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h3 class="font-display font-semibold text-xl text-slate-900">Grafik Keuangan</h3>
                <p class="text-sm text-slate-500 mt-1">Pergerakan pemasukan dan pengeluaran selama 6 bulan terakhir.</p>
            </div>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-50 border border-slate-200 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Live trend
            </div>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white p-6 md:p-8 rounded-[28px] shadow-soft border border-white/80">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h3 class="font-display font-semibold text-xl text-slate-900">Transaksi Terbaru</h3>
                <p class="text-sm text-slate-500 mt-1">Lima aktivitas terakhir yang paling relevan.</p>
            </div>
            <div class="h-11 w-11 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500">
                <i class="fas fa-clock-rotate-left"></i>
            </div>
        </div>
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
                        class="flex items-center justify-between p-4 hover:bg-slate-50 rounded-[22px] transition-colors cursor-pointer border border-transparent hover:border-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 <?= $bg ?> rounded-2xl flex items-center justify-center shadow-inner">
                                <i class="fas <?= $icon ?>"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800 line-clamp-1">
                                    <?= $row['keterangan'] ?>
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
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
                                <span class="text-[10px] uppercase px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full tracking-[0.16em]">
                                    <?= $row['nama_unit'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php }
            } else { ?>
                <div class="text-center text-slate-400 py-8">Belum ada transaksi</div>
            <?php } ?>
        </div>
        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <a href="transaksi.php" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Lihat Semua
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

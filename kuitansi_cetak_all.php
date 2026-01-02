<?php
require_once 'config/koneksi.php';

if (!isset($_SESSION['login'])) { exit; }

$id_santri = $_GET['id_santri'] ?? null;
if (!$id_santri) exit('ID tidak ditemukan');

// Fetch Santri Info
$q_santri = "SELECT s.*, u.nama_unit 
              FROM santri s 
              JOIN units u ON s.id_unit = u.id 
              WHERE s.id = '$id_santri'";
$res_santri = mysqli_query($koneksi, $q_santri);
$santri = mysqli_fetch_assoc($res_santri);

// Fetch All Bills
$q_bills = "SELECT * FROM tagihan WHERE id_santri = '$id_santri' ORDER BY tanggal_dibuat DESC";
$res_bills = mysqli_query($koneksi, $q_bills);

// Summary
$q_summary = "SELECT SUM(nominal) as total_nominal, SUM(terbayar) as total_terbayar FROM tagihan WHERE id_santri = '$id_santri'";
$res_summary = mysqli_query($koneksi, $q_summary);
$summary = mysqli_fetch_assoc($res_summary);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Tagihan Santri</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 10pt; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .student-info { margin-bottom: 20px; }
        .student-info table { width: 100%; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #000; padding: 8px; text-align: left; }
        table.data th { background: #f0f0f0; }
        .summary-box { margin-top: 20px; padding: 15px; border: 1px solid #000; background: #fafafa; }
        .footer { margin-top: 50px; display: flex; justify-content: flex-end; }
        .footer div { text-align: center; width: 250px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

<div class="header">
    <h1 style="margin:0">SIKEP PESANTREN</h1>
    <p style="margin:5px 0">SURAT REKAPITULASI TAGIHAN SANTRI</p>
    <p style="font-size: 9pt;">Unit <?= $santri['nama_unit'] ?> | Tanggal: <?= date('d/m/Y') ?></p>
</div>

<div class="student-info">
    <table>
        <tr>
            <td width="150">Nama Santri</td>
            <td width="20">:</td>
            <td><strong><?= strtoupper($santri['nama']) ?></strong></td>
        </tr>
        <tr>
            <td>NIS</td>
            <td>:</td>
            <td><?= $santri['nis'] ?></td>
        </tr>
        <tr>
            <td>Kelas / Unit</td>
            <td>:</td>
            <td><?= $santri['kelas'] ?> / <?= $santri['nama_unit'] ?></td>
        </tr>
    </table>
</div>

<table class="data">
    <thead>
        <tr>
            <th>No</th>
            <th>Item Tagihan</th>
            <th>Tanggal Tagihan</th>
            <th align="right">Nominal</th>
            <th align="right">Terbayar</th>
            <th align="right">Sisa</th>
            <th align="center">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while($b = mysqli_fetch_assoc($res_bills)): 
        $sisa = $b['nominal'] - $b['terbayar'];
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $b['judul'] ?></td>
            <td><?= date('d/m/Y', strtotime($b['tanggal_dibuat'])) ?></td>
            <td align="right">Rp <?= number_format($b['nominal'], 0, ',', '.') ?></td>
            <td align="right">Rp <?= number_format($b['terbayar'], 0, ',', '.') ?></td>
            <td align="right">Rp <?= number_format($sisa, 0, ',', '.') ?></td>
            <td align="center"><?= strtoupper($b['status']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
    <tfoot>
        <tr style="font-weight: bold; background: #eee;">
            <td colspan="3" align="right">TOTAL KESELURUHAN</td>
            <td align="right">Rp <?= number_format($summary['total_nominal'], 0, ',', '.') ?></td>
            <td align="right">Rp <?= number_format($summary['total_terbayar'], 0, ',', '.') ?></td>
            <td align="right">Rp <?= number_format($summary['total_nominal'] - $summary['total_terbayar'], 0, ',', '.') ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="summary-box">
    <strong>Catatan:</strong><br>
    Total kekurangan pembayaran yang harus segera diselesaikan adalah 
    <span style="color: red; font-size: 14pt; font-weight: bold;">
        Rp <?= number_format($summary['total_nominal'] - $summary['total_terbayar'], 0, ',', '.') ?>
    </span>
</div>

<div class="footer">
    <div>
        <p>Bendahara Pesantren,</p>
        <br><br><br>
        <p>( _______________________ )</p>
    </div>
</div>

<div class="no-print" style="margin-top: 20px; text-align: center;">
    <button onclick="window.print()">Cetak</button>
    <button onclick="window.close()">Tutup</button>
</div>

</body>
</html>

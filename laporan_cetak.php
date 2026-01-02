<?php
require_once 'config/koneksi.php';

// Auth Check Manual since we don't include header
if (!isset($_SESSION['login'])) {
    exit;
}

$role = $_SESSION['role'];
$id_unit_session = $_SESSION['id_unit'];

// Default Dates
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');

// Unit Filter
if ($role == 'super_admin') {
    $id_unit_filter = $_GET['id_unit'] ?? 'all';
} else {
    $id_unit_filter = $id_unit_session;
}

// Build Query
$where = "WHERE t.tanggal BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'";

$unit_name_label = "SEMUA UNIT";
if ($id_unit_filter != 'all') {
    $where .= " AND t.id_unit = '$id_unit_filter'";
    // Fetch unit name for title
    $q_u = mysqli_query($koneksi, "SELECT nama_unit FROM units WHERE id='$id_unit_filter'");
    $r_u = mysqli_fetch_assoc($q_u);
    $unit_name_label = "UNIT " . strtoupper($r_u['nama_unit']);
}

$query = "SELECT t.*, u.nama_unit 
          FROM transaksi t 
          JOIN units u ON t.id_unit = u.id 
          $where 
          ORDER BY t.tanggal ASC";

$result = mysqli_query($koneksi, $query);

$total_masuk = 0;
$total_keluar = 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .header h1 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
        }

        .details {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 10pt;
        }

        th {
            background-color: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer-sig {
            margin-top: 40px;
            text-align: right;
        }

        .footer-sig div {
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header">
        <h1>Laporan Keuangan Pesantren</h1>
        <p><strong>
                <?= $unit_name_label ?>
            </strong></p>
        <p>Periode:
            <?= date('d M Y', strtotime($tgl_awal)) ?> s/d
            <?= date('d M Y', strtotime($tgl_akhir)) ?>
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th>Keterangan</th>
                <th width="15%">Pemasukan</th>
                <th width="15%">Pengeluaran</th>
                <?php if ($id_unit_filter == 'all'): ?>
                    <th width="10%">Unit</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)):
                if ($row['jenis'] == 'Masuk') {
                    $total_masuk += $row['nominal'];
                    $masuk = $row['nominal'];
                    $keluar = 0;
                } else {
                    $total_keluar += $row['nominal'];
                    $masuk = 0;
                    $keluar = $row['nominal'];
                }
                ?>
                <tr>
                    <td class="text-center">
                        <?= $no++ ?>
                    </td>
                    <td class="text-center">
                        <?= date('d/m/Y', strtotime($row['tanggal'])) ?>
                    </td>
                    <td>
                        <?= $row['keterangan'] ?>
                    </td>
                    <td class="text-right">
                        <?= $masuk > 0 ? number_format($masuk, 0, ',', '.') : '-' ?>
                    </td>
                    <td class="text-right">
                        <?= $keluar > 0 ? number_format($keluar, 0, ',', '.') : '-' ?>
                    </td>
                    <?php if ($id_unit_filter == 'all'): ?>
                        <td class="text-center">
                            <?= $row['nama_unit'] ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>
                        <?= number_format($total_masuk, 0, ',', '.') ?>
                    </strong></td>
                <td class="text-right"><strong>
                        <?= number_format($total_keluar, 0, ',', '.') ?>
                    </strong></td>
                <?php if ($id_unit_filter == 'all'): ?>
                    <td></td>
                <?php endif; ?>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>SALDO PERIODE INI</strong></td>
                <td colspan="2" class="text-center"><strong>
                        <?= number_format($total_masuk - $total_keluar, 0, ',', '.') ?>
                    </strong></td>
                <?php if ($id_unit_filter == 'all'): ?>
                    <td></td>
                <?php endif; ?>
            </tr>
        </tfoot>
    </table>

    <div class="footer-sig">
        <div>
            <p>Diketahui Oleh,</p>
            <br><br><br>
            <p><strong>____________________</strong></p>
            <p>Kepala Pesantren</p>
        </div>
    </div>

</body>

</html>
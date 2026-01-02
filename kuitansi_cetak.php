<?php
require_once 'config/koneksi.php';

// Auth Check Manual
if (!isset($_SESSION['login'])) {
    exit;
}

$pembayaran_id = $_GET['pembayaran_id'] ?? null;
$tagihan_id = $_GET['tagihan_id'] ?? null;

if ($pembayaran_id) {
    // Single Receipt
    $q = "SELECT pt.*, t.judul, t.nominal as total_tagihan, s.nama as nama_santri, s.kelas, s.nis, u.nama_unit, us.username as petugas, t.terbayar as total_terbayar
          FROM pembayaran_tagihan pt
          JOIN tagihan t ON pt.id_tagihan = t.id
          JOIN santri s ON t.id_santri = s.id
          JOIN units u ON t.id_unit = u.id
          JOIN transaksi tr ON pt.id_transaksi = tr.id
          JOIN users us ON tr.id_user = us.id
          WHERE pt.id = '$pembayaran_id'";
    $mode = 'single';
} elseif ($tagihan_id) {
    // Full Rekap Receipt
    $q = "SELECT t.judul, t.nominal as total_tagihan, t.terbayar as total_terbayar, t.status, s.nama as nama_santri, s.kelas, s.nis, u.nama_unit
          FROM tagihan t
          JOIN santri s ON t.id_santri = s.id
          JOIN units u ON t.id_unit = u.id
          WHERE t.id = '$tagihan_id'";
    $mode = 'rekap';
} else {
    exit('ID tidak ditemukan');
}

$res = mysqli_query($koneksi, $q);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    exit('Data tidak ditemukan');
}

// Fetch history for rekap mode
if ($mode == 'rekap') {
    $q_h = "SELECT pt.*, us.username as petugas FROM pembayaran_tagihan pt 
            JOIN transaksi tr ON pt.id_transaksi = tr.id
            JOIN users us ON tr.id_user = us.id
            WHERE pt.id_tagihan = '$tagihan_id' ORDER BY pt.tanggal ASC";
    $res_h = mysqli_query($koneksi, $q_h);
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kuitansi Pembayaran</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            color: #000;
            padding: 20px;
        }

        .receipt-box {
            width: 500px;
            border: 2px dashed #000;
            padding: 20px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            border-bottom: 1px double #000;
            margin-bottom: 15px;
            padding-bottom: 5px;
        }

        .header h2 {
            margin: 0;
            font-size: 14pt;
        }

        .info {
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .info table {
            width: 100%;
            border-collapse: collapse;
        }

        .info td {
            vertical-align: top;
        }

        .line {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }

        .amount-box {
            text-align: right;
            margin-top: 15px;
        }

        .amount-box .total {
            font-size: 16pt;
            font-weight: bold;
            border: 2px solid #000;
            display: inline-block;
            padding: 5px 15px;
            margin-top: 5px;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .footer div {
            text-align: center;
            width: 45%;
        }

        .stamp {
            font-size: 8pt;
            color: #555;
            margin-top: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="receipt-box">
        <div class="header">
            <h2>PESANTREN SIKEP</h2>
            <p>Unit
                <?= $data['nama_unit'] ?>
            </p>
            <p style="font-size: 8pt;">
                <?= date('d/m/Y H:i:s') ?>
            </p>
        </div>

        <div class="info">
            <table>
                <tr>
                    <td width="30%">Nama</td>
                    <td width="5%">:</td>
                    <td><strong>
                            <?= strtoupper($data['nama_santri']) ?>
                        </strong> (
                        <?= $data['kelas'] ?>)
                    </td>
                </tr>
                <tr>
                    <td>NIS</td>
                    <td>:</td>
                    <td>
                        <?= $data['nis'] ?>
                    </td>
                </tr>
                <tr>
                    <td>Tagihan</td>
                    <td>:</td>
                    <td>
                        <?= $data['judul'] ?> (Rp
                        <?= number_format($data['total_tagihan'], 0, ',', '.') ?>)
                    </td>
                </tr>
            </table>
        </div>

        <div class="line"></div>

        <div class="content">
            <?php if ($mode == 'single'): ?>
                <p><strong>DETAIL PEMBAYARAN SAAT INI:</strong></p>
                <p style="padding-left: 20px;">
                    Tgl:
                    <?= date('d/m/Y H:i', strtotime($data['tanggal'])) ?><br>
                    Ket:
                    <?= $data['keterangan'] ?: 'Pembayaran Reguler' ?><br>
                </p>
                <div class="amount-box">
                    <span>NOMINAL BAYAR:</span><br>
                    <div class="total">Rp
                        <?= number_format($data['jumlah'], 0, ',', '.') ?>
                    </div>
                </div>
                <div class="line"></div>
                <p style="font-size: 8pt; text-align: right;">Sisa Tagihan: Rp
                    <?= number_format($data['total_tagihan'] - $data['total_terbayar'], 0, ',', '.') ?>
                </p>
            <?php else: ?>
                <p><strong>REKAP PEMBAYARAN:</strong></p>
                <table style="width: 100%; border: 1px solid #000; font-size: 9pt;">
                    <tr style="background: #f0f0f0;">
                        <th align="left">Tgl</th>
                        <th align="right">Jumlah</th>
                    </tr>
                    <?php while ($h = mysqli_fetch_assoc($res_h)): ?>
                        <tr>
                            <td>
                                <?= date('d/m/y', strtotime($h['tanggal'])) ?>
                            </td>
                            <td align="right">
                                <?= number_format($h['jumlah'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <div class="amount-box">
                    <span>TOTAL TERBAYAR:</span><br>
                    <div class="total">Rp
                        <?= number_format($data['total_terbayar'], 0, ',', '.') ?>
                    </div>
                </div>
                <p style="text-align: right; margin-top: 5px;">Status: <strong>
                        <?= strtoupper($data['status']) ?>
                    </strong></p>
            <?php endif; ?>
        </div>

        <div class="footer">
            <div>
                <p>Penyetor,</p>
                <br><br>
                <p>( ............ )</p>
            </div>
            <div>
                <p>Petugas,</p>
                <br><br>
                <p>(
                    <?= ucfirst($data['petugas'] ?? 'Admin') ?> )
                </p>
            </div>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 10px 20px;">Tutup</button>
    </div>

</body>

</html>
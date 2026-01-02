<?php
// alumni_export.php
require_once 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    exit;
}

$role = $_SESSION['role'];
$id_unit = $_SESSION['id_unit'];

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Alumni_" . date('Y-m-d') . ".xls");

$where = "WHERE s.status = 'Lulus'";
if ($role != 'super_admin') {
    $where .= " AND s.id_unit = '$id_unit'";
}

$query = "SELECT s.*, u.nama_unit, 
                 IFNULL(SUM(t.nominal - t.terbayar), 0) as sisa_tagihan
          FROM santri s 
          JOIN units u ON s.id_unit = u.id 
          LEFT JOIN tagihan t ON s.id = t.id_santri
          $where 
          GROUP BY s.id 
          ORDER BY s.nama ASC";

$result = mysqli_query($koneksi, $query);
?>

<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>No</th>
            <th>NIS</th>
            <th>Nama Alumni</th>
            <th>Terakhir di Kelas</th>
            <th>Unit</th>
            <th>Sisa Tagihan (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)):
            ?>
            <tr>
                <td>
                    <?= $no++ ?>
                </td>
                <td>'
                    <?= $row['nis'] ?>
                </td> <!-- Quote to keep leading zeros if any -->
                <td>
                    <?= strtoupper($row['nama']) ?>
                </td>
                <td>
                    <?= $row['kelas'] ?>
                </td>
                <td>
                    <?= $row['nama_unit'] ?>
                </td>
                <td align="right">
                    <?= $row['sisa_tagihan'] ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
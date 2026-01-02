<?php
// santri_export.php
require_once 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    exit;
}

$role = $_SESSION['role'];
$id_unit = $_SESSION['id_unit'];

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Santri_" . date('Y-m-d') . ".xls");

$where = "WHERE s.status = 'Aktif'";
if ($role != 'super_admin') {
    $where .= " AND s.id_unit = '$id_unit'";
}

$query = "SELECT s.*, u.nama_unit FROM santri s JOIN units u ON s.id_unit = u.id $where ORDER BY s.kelas, s.nama";
$result = mysqli_query($koneksi, $query);
?>

<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama Lengkap</th>
            <th>Kelas</th>
            <th>Unit</th>
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
                <td>
                    <?= $row['nis'] ?>
                </td>
                <td>
                    <?= $row['nama'] ?>
                </td>
                <td>
                    <?= $row['kelas'] ?>
                </td>
                <td>
                    <?= $row['nama_unit'] ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
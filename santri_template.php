<?php
// santri_template.php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Template_Import_Santri.csv');

// Clean buffer to avoid interference from other outputs
if (ob_get_length())
    ob_clean();

$output = fopen('php://output', 'w');

// Add "sep=;" to help Excel detect the separator automatically
fwrite($output, "sep=;\n");

// Use semicolon (;) as delimiter for better Excel compatibility in Indonesia
fputcsv($output, array('NIS', 'Nama Lengkap', 'Kelas', 'Status (Aktif/Lulus)'), ';');

// Contoh Data
fputcsv($output, array('12345678', 'Contoh Nama Santri', '7A', 'Aktif'), ';');
fputcsv($output, array('87654321', 'Contoh Nama Alumni', '9C', 'Lulus'), ';');

fclose($output);
exit;
?>
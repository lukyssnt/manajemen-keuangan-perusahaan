<?php
require_once __DIR__ . '/config/koneksi.php';

$size = isset($_GET['size']) ? (int) $_GET['size'] : 192;
$size = max(96, min(512, $size));

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');

$q_conf = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$sys = mysqli_fetch_assoc($q_conf) ?: [];

$image = imagecreatetruecolor($size, $size);
imagesavealpha($image, true);
$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $transparent);

for ($y = 0; $y < $size; $y++) {
    $ratio = $size > 1 ? $y / ($size - 1) : 0;
    $red = (int) round(16 + (20 - 16) * $ratio);
    $green = (int) round(185 + (184 - 185) * $ratio);
    $blue = (int) round(129 + (166 - 129) * $ratio);
    $lineColor = imagecolorallocate($image, $red, $green, $blue);
    imageline($image, 0, $y, $size, $y, $lineColor);
}

$overlay = imagecolorallocatealpha($image, 255, 255, 255, 108);
imagefilledellipse($image, (int) ($size * 0.74), (int) ($size * 0.24), (int) ($size * 0.42), (int) ($size * 0.42), $overlay);

$logoPath = '';
if (!empty($sys['logo'])) {
    $candidate = __DIR__ . '/uploads/' . basename($sys['logo']);
    if (is_file($candidate)) {
        $logoPath = $candidate;
    }
}

if ($logoPath !== '') {
    $logoBlob = @file_get_contents($logoPath);
    $logoImage = $logoBlob ? @imagecreatefromstring($logoBlob) : false;

    if ($logoImage !== false) {
        $srcWidth = imagesx($logoImage);
        $srcHeight = imagesy($logoImage);
        $targetSize = (int) round($size * 0.56);
        $targetX = (int) round(($size - $targetSize) / 2);
        $targetY = (int) round(($size - $targetSize) / 2);

        $badgeSize = (int) round($targetSize * 1.1);
        $badgeX = (int) round(($size - $badgeSize) / 2);
        $badgeY = (int) round(($size - $badgeSize) / 2);
        $badgeColor = imagecolorallocatealpha($image, 255, 255, 255, 36);
        imagefilledellipse($image, $badgeX + (int) ($badgeSize / 2), $badgeY + (int) ($badgeSize / 2), $badgeSize, $badgeSize, $badgeColor);

        imagealphablending($image, true);
        imagecopyresampled($image, $logoImage, $targetX, $targetY, 0, 0, $targetSize, $targetSize, $srcWidth, $srcHeight);
        imagedestroy($logoImage);
    }
}

if ($logoPath === '' || !isset($logoImage) || $logoImage === false) {
    $white = imagecolorallocate($image, 255, 255, 255);
    $softWhite = imagecolorallocatealpha($image, 255, 255, 255, 78);

    $baseY = (int) round($size * 0.66);
    imagefilledrectangle($image, (int) round($size * 0.26), $baseY, (int) round($size * 0.74), (int) round($size * 0.74), $white);
    imagefilledarc($image, (int) round($size * 0.50), $baseY, (int) round($size * 0.48), (int) round($size * 0.42), 180, 360, $white, IMG_ARC_PIE);
    imagefilledrectangle($image, (int) round($size * 0.19), (int) round($size * 0.34), (int) round($size * 0.27), (int) round($size * 0.74), $white);
    imagefilledellipse($image, (int) round($size * 0.23), (int) round($size * 0.28), (int) round($size * 0.11), (int) round($size * 0.11), $white);

    imagefilledrectangle($image, (int) round($size * 0.44), (int) round($size * 0.55), (int) round($size * 0.56), (int) round($size * 0.74), $softWhite);
    imagefilledellipse($image, (int) round($size * 0.50), (int) round($size * 0.55), (int) round($size * 0.12), (int) round($size * 0.12), $softWhite);
}

imagepng($image);
imagedestroy($image);

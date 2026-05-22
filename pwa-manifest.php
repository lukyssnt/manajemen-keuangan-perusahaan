<?php
require_once __DIR__ . '/config/koneksi.php';

header('Content-Type: application/manifest+json; charset=utf-8');

$q_conf = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$sys = mysqli_fetch_assoc($q_conf) ?: [];

$appName = trim($sys['nama_aplikasi'] ?? '') ?: 'SIKEP';
$shortName = substr($appName, 0, 12);
$startUrl = base_url('index.php');
$scope = rtrim(base_url(''), '/') . '/';

$manifest = [
    'id' => $startUrl,
    'name' => $appName,
    'short_name' => $shortName,
    'description' => 'Aplikasi manajemen keuangan pondok berbasis web yang siap dipakai seperti aplikasi mobile.',
    'lang' => 'id-ID',
    'dir' => 'ltr',
    'start_url' => $startUrl,
    'scope' => $scope,
    'display' => 'standalone',
    'orientation' => 'portrait',
    'background_color' => '#f8fafc',
    'theme_color' => '#10b981',
    'icons' => [
        [
            'src' => base_url('pwa-icon.php?size=192'),
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any maskable',
        ],
        [
            'src' => base_url('pwa-icon.php?size=512'),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any maskable',
        ],
    ],
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

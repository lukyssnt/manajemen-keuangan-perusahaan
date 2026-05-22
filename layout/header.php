<?php
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: " . base_url('login.php'));
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'];
$user_id = $_SESSION['id_user'];

$page_groups = [
    'dashboard' => ['index.php'],
    'santri' => ['santri.php', 'santri_tambah.php', 'santri_edit.php', 'santri_import.php'],
    'alumni' => ['alumni.php'],
    'tagihan' => ['tagihan.php', 'tagihan_buat.php', 'tagihan_bayar.php', 'tagihan_detail.php'],
    'transaksi' => ['transaksi.php', 'transaksi_tambah.php'],
    'laporan' => ['laporan.php'],
    'users' => ['users.php', 'users_tambah.php', 'users_edit.php'],
    'audit' => ['audit_log.php'],
    'pengaturan' => ['pengaturan.php'],
    'profil' => ['profil.php'],
];

$section_key = 'dashboard';
foreach ($page_groups as $key => $pages) {
    if (in_array($current_page, $pages, true)) {
        $section_key = $key;
        break;
    }
}

$section_meta = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'fas fa-home', 'desc' => 'Ringkasan aktivitas dan statistik utama.'],
    'santri' => ['label' => 'Data Santri', 'icon' => 'fas fa-user-graduate', 'desc' => 'Kelola data santri, import, dan pembaruan kelas.'],
    'alumni' => ['label' => 'Data Alumni', 'icon' => 'fas fa-user-graduate', 'desc' => 'Pantau alumni dan tagihan lanjutan.'],
    'tagihan' => ['label' => 'Tagihan Santri', 'icon' => 'fas fa-file-invoice-dollar', 'desc' => 'Monitor tagihan, status lunas, dan detail pembayaran.'],
    'transaksi' => ['label' => 'Transaksi Kas', 'icon' => 'fas fa-exchange-alt', 'desc' => 'Catat pemasukan dan pengeluaran kas harian.'],
    'laporan' => ['label' => 'Laporan', 'icon' => 'fas fa-chart-line', 'desc' => 'Susun rekap transaksi dan performa keuangan.'],
    'users' => ['label' => 'Manajemen User', 'icon' => 'fas fa-users-cog', 'desc' => 'Atur akun, role, dan akses pengguna sistem.'],
    'audit' => ['label' => 'Audit Log', 'icon' => 'fas fa-history', 'desc' => 'Lacak aktivitas penting di dalam aplikasi.'],
    'pengaturan' => ['label' => 'Pengaturan Web', 'icon' => 'fas fa-cog', 'desc' => 'Sesuaikan identitas dan konfigurasi aplikasi.'],
    'profil' => ['label' => 'Profil Saya', 'icon' => 'fas fa-user-circle', 'desc' => 'Perbarui informasi akun pribadi Anda.'],
];

$active_section = $section_meta[$section_key];

$nav_groups = [
    [
        'label' => 'Utama',
        'items' => [
            ['href' => 'index.php', 'key' => 'dashboard', 'icon' => 'fas fa-home', 'text' => 'Dashboard'],
            ['href' => 'santri.php', 'key' => 'santri', 'icon' => 'fas fa-user-graduate', 'text' => 'Data Santri'],
            ['href' => 'alumni.php', 'key' => 'alumni', 'icon' => 'fas fa-user-graduate', 'text' => 'Data dan Tagihan Alumni'],
        ]
    ],
    [
        'label' => 'Keuangan',
        'items' => [
            ['href' => 'tagihan.php', 'key' => 'tagihan', 'icon' => 'fas fa-file-invoice-dollar', 'text' => 'Tagihan Santri'],
            ['href' => 'transaksi.php', 'key' => 'transaksi', 'icon' => 'fas fa-exchange-alt', 'text' => 'Transaksi Kas'],
            ['href' => 'laporan.php', 'key' => 'laporan', 'icon' => 'fas fa-file-invoice-dollar', 'text' => 'Laporan'],
        ]
    ],
];

if ($user_role == 'super_admin') {
    $nav_groups[] = [
        'label' => 'Administrasi',
        'items' => [
            ['href' => 'users.php', 'key' => 'users', 'icon' => 'fas fa-users-cog', 'text' => 'Manajemen User'],
            ['href' => 'audit_log.php', 'key' => 'audit', 'icon' => 'fas fa-history', 'text' => 'Audit Log'],
            ['href' => 'pengaturan.php', 'key' => 'pengaturan', 'icon' => 'fas fa-cog', 'text' => 'Pengaturan Web'],
        ]
    ];
}

$nav_groups[] = [
    'label' => 'Akun',
    'items' => [
        ['href' => 'profil.php', 'key' => 'profil', 'icon' => 'fas fa-user-circle', 'text' => 'Profil Saya'],
    ]
];

// Fetch System Settings
$q_conf = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$sys = mysqli_fetch_assoc($q_conf);

// Fetch User Info (Full Name)
$q_u = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id'");
$u_info = mysqli_fetch_assoc($q_u);
$display_name = $u_info['nama'] ?: $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $sys['nama_aplikasi'] ?: 'SIKEP' ?> - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- jQuery & DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Poppins', 'Inter', 'sans-serif']
                    },
                    boxShadow: {
                        soft: '0 24px 60px -32px rgba(15, 23, 42, 0.22)',
                        panel: '0 18px 42px -28px rgba(15, 23, 42, 0.18)',
                        emerald: '0 18px 36px -24px rgba(5, 150, 105, 0.45)'
                    }
                }
            }
        };
    </script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;600;700&display=swap');

        :root {
            --emerald-50: #ecfdf5;
            --emerald-100: #d1fae5;
            --emerald-400: #34d399;
            --emerald-500: #10b981;
            --emerald-600: #059669;
            --teal-400: #2dd4bf;
            --teal-500: #14b8a6;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-500: #64748b;
            --slate-700: #334155;
            --slate-900: #0f172a;
            --motion-fast: 160ms;
            --motion-base: 220ms;
            --motion-slow: 320ms;
            --motion-ease: cubic-bezier(0.22, 1, 0.36, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            background:
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.1), transparent 22%),
                radial-gradient(circle at left 15%, rgba(45, 212, 191, 0.08), transparent 18%),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 55%, #eef2f7 100%);
            color: var(--slate-700);
        }

        h1,
        h2,
        h3,
        h4,
        .font-display {
            font-family: 'Poppins', 'Inter', sans-serif;
            letter-spacing: -0.02em;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.45) transparent;
        }

        *::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        *::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.35);
            border-radius: 999px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        *::-webkit-scrollbar-track {
            background: transparent;
        }

        /* Custom DataTables for Tailwind */
        .dataTables_wrapper {
            color: var(--slate-700);
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--slate-200) !important;
            border-radius: 0.95rem !important;
            min-height: 2.85rem;
            padding: 0.45rem 0.9rem !important;
            outline: none !important;
            background: rgba(255, 255, 255, 0.92) !important;
            color: var(--slate-700) !important;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--emerald-500) !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12) !important;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            color: var(--slate-500) !important;
            font-size: 0.92rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1rem !important;
            display: flex;
            gap: 0.35rem;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, var(--emerald-500), var(--teal-500)) !important;
            color: white !important;
            border: none !important;
            border-radius: 999px !important;
            box-shadow: 0 10px 24px -16px rgba(5, 150, 105, 0.8) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 999px !important;
            border: 1px solid transparent !important;
            min-width: 2.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--emerald-50) !important;
            color: var(--emerald-600) !important;
            border: none !important;
            border-radius: 999px !important;
        }

        .nav-item {
            position: relative;
            margin: 0 0.75rem;
            border-radius: 1.15rem;
            transition: background-color var(--motion-base) var(--motion-ease),
                        color var(--motion-base) var(--motion-ease),
                        transform var(--motion-base) var(--motion-ease),
                        box-shadow var(--motion-base) var(--motion-ease);
        }

        .nav-item i {
            transition: transform var(--motion-base) var(--motion-ease),
                        color var(--motion-base) var(--motion-ease);
            width: 1.35rem;
            text-align: center;
        }

        .nav-item:hover {
            transform: translateX(2px);
        }

        .nav-label {
            color: var(--slate-500);
            font-size: 0.69rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            margin: 1.1rem 1.4rem 0.55rem;
            padding-top: 0.9rem;
            border-top: 1px solid rgba(226, 232, 240, 0.72);
        }

        .nav-group:first-child .nav-label {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
        }

        .nav-item-text {
            line-height: 1.35;
            font-size: 0.98rem;
        }

        .nav-item-meta {
            display: block;
            margin-top: 0.1rem;
            font-size: 0.72rem;
            color: var(--slate-500);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.16), rgba(45, 212, 191, 0.12));
            color: #047857;
            box-shadow: inset 0 0 0 1px rgba(16, 185, 129, 0.12), 0 20px 38px -30px rgba(5, 150, 105, 0.8);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: -0.4rem;
            top: 0.8rem;
            bottom: 0.8rem;
            width: 0.3rem;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--emerald-500) 0%, var(--teal-500) 100%);
        }

        .nav-item.active i {
            color: var(--emerald-600);
            transform: scale(1.08);
        }

        .sidebar-shell {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.98) 72%, rgba(240, 253, 250, 0.96) 100%);
            backdrop-filter: blur(22px);
            border-right: 1px solid rgba(226, 232, 240, 0.85);
            box-shadow: 20px 0 50px -42px rgba(15, 23, 42, 0.22);
        }

        .sidebar-brand {
            min-height: 5.75rem;
        }

        .sidebar-logo {
            height: 2.8rem;
            width: 2.8rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(45, 212, 191, 0.15));
            border: 1px solid rgba(16, 185, 129, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-footer {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0), rgba(236, 253, 245, 0.55));
        }

        .topbar-shell {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(22px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 12px 30px -28px rgba(15, 23, 42, 0.22);
        }

        .section-chip {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.14), rgba(45, 212, 191, 0.16));
            border: 1px solid rgba(16, 185, 129, 0.16);
            color: #047857;
        }

        .section-glow {
            box-shadow: 0 18px 40px -28px rgba(5, 150, 105, 0.7);
        }

        .sidebar-badge {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.13), rgba(45, 212, 191, 0.2));
            border: 1px solid rgba(16, 185, 129, 0.14);
            color: var(--emerald-600);
        }

        .sidebar-close {
            height: 2.5rem;
            width: 2.5rem;
            border-radius: 0.9rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            background: rgba(255, 255, 255, 0.86);
            color: var(--slate-500);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Custom Animations */
        @keyframes fadeInUpCustom {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUpCustom var(--motion-slow) var(--motion-ease) forwards;
            will-change: opacity, transform;
        }

        .stagger-1 {
            animation-delay: 0.02s;
        }

        .stagger-2 {
            animation-delay: 0.04s;
        }

        .stagger-3 {
            animation-delay: 0.06s;
        }

        .stagger-4 {
            animation-delay: 0.08s;
        }

        /* Card Hover Effect */
        .hover-card {
            transition: transform var(--motion-base) var(--motion-ease),
                        box-shadow var(--motion-base) var(--motion-ease),
                        border-color var(--motion-base) var(--motion-ease);
        }

        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 42px -30px rgba(15, 23, 42, 0.2);
        }

        main {
            padding: 2rem;
        }

        main .bg-white.rounded-xl,
        main .bg-white.rounded-2xl,
        main .bg-white.rounded-3xl {
            border-radius: 1.65rem !important;
            box-shadow: 0 24px 55px -34px rgba(15, 23, 42, 0.22);
            border-color: rgba(226, 232, 240, 0.95) !important;
            transition: transform var(--motion-base) var(--motion-ease),
                        box-shadow var(--motion-base) var(--motion-ease),
                        border-color var(--motion-base) var(--motion-ease);
            background: rgba(255, 255, 255, 0.96);
        }

        main .bg-white.rounded-xl:hover,
        main .bg-white.rounded-2xl:hover,
        main .bg-white.rounded-3xl:hover {
            transform: translateY(-2px);
            box-shadow: 0 28px 56px -38px rgba(15, 23, 42, 0.22);
        }

        main form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        main form select,
        main form textarea {
            min-height: 3rem;
            border-radius: 1rem !important;
            border-color: #d1d5db !important;
            transition: border-color var(--motion-fast) var(--motion-ease),
                        box-shadow var(--motion-fast) var(--motion-ease),
                        transform var(--motion-fast) var(--motion-ease),
                        background-color var(--motion-fast) var(--motion-ease);
        }

        main form textarea {
            min-height: 6rem;
        }

        main form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):focus,
        main form select:focus,
        main form textarea:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12), 0 10px 30px -18px rgba(16, 185, 129, 0.65) !important;
            transform: translateY(-0.5px);
            outline: none !important;
        }

        main form input[type="file"] {
            padding-top: 0.7rem !important;
            padding-bottom: 0.7rem !important;
            background: rgba(248, 250, 252, 0.82);
        }

        main form button,
        main form a[class*="bg-"],
        main .action-link,
        .logout-link {
            transition: transform var(--motion-fast) var(--motion-ease),
                        box-shadow var(--motion-fast) var(--motion-ease),
                        background-color var(--motion-fast) var(--motion-ease),
                        color var(--motion-fast) var(--motion-ease);
        }

        main form button:hover,
        main form a[class*="bg-"]:hover,
        main .action-link:hover,
        .logout-link:hover {
            transform: translateY(-0.5px);
        }

        main form button[class*="bg-emerald"],
        main form a[class*="bg-emerald"] {
            box-shadow: 0 18px 30px -18px rgba(5, 150, 105, 0.75);
        }

        main button[class*="bg-emerald-600"],
        main a[class*="bg-emerald-600"],
        main button[class*="from-emerald"],
        main a[class*="from-emerald"] {
            background-image: linear-gradient(135deg, var(--emerald-500), var(--teal-500));
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #fff !important;
        }

        main button[class*="bg-gray-100"],
        main a[class*="bg-gray-100"] {
            background: rgba(255, 255, 255, 0.88) !important;
            border: 1px solid rgba(226, 232, 240, 0.95);
            color: var(--slate-700) !important;
            box-shadow: 0 14px 28px -24px rgba(15, 23, 42, 0.18);
        }

        main table {
            border-collapse: separate;
            border-spacing: 0;
        }

        main table thead th {
            background: rgba(248, 250, 252, 0.94);
            color: var(--slate-500);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            position: sticky;
            top: 0;
            z-index: 1;
        }

        main table tbody tr {
            transition: background-color var(--motion-fast) var(--motion-ease),
                        transform var(--motion-fast) var(--motion-ease);
        }

        main table tbody tr:hover {
            background: rgba(236, 253, 245, 0.55);
        }

        .premium-button {
            border-radius: 1rem;
            background: linear-gradient(135deg, var(--emerald-500), var(--teal-500));
            color: #fff;
            box-shadow: 0 20px 36px -24px rgba(5, 150, 105, 0.7);
        }

        .premium-button:hover {
            box-shadow: 0 28px 42px -26px rgba(5, 150, 105, 0.72);
        }

        .ghost-button {
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(226, 232, 240, 0.9);
            color: var(--slate-700);
            box-shadow: 0 14px 30px -24px rgba(15, 23, 42, 0.15);
        }

        .mobile-toolbar {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .mobile-toolbar-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
            width: 100%;
        }

        .mobile-panel {
            padding: 1.25rem;
        }

        .mobile-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .mobile-table {
            min-width: 760px;
        }

        .mobile-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.5rem;
        }

        .mobile-button-wide {
            width: auto;
        }

        /* Custom SweetAlert Emerald Theme */
        .swal2-emerald-popup {
            border-radius: 22px !important;
        }

        .swal2-emerald-confirm {
            background: linear-gradient(135deg, var(--emerald-500), var(--teal-500)) !important;
            box-shadow: 0 18px 34px -22px rgba(5, 150, 105, 0.75) !important;
        }

        @media (max-width: 1023px) {
            main {
                padding: 1.25rem;
            }
        }

        @media (max-width: 767px) {
            .mobile-drawer {
                position: fixed;
                inset: 0 auto 0 0;
                width: min(82vw, 20rem);
                display: flex !important;
                transform: translateX(-105%);
                transition: transform var(--motion-slow) var(--motion-ease);
                z-index: 50;
            }

            .mobile-drawer.open {
                transform: translateX(0);
            }

            .nav-label {
                margin-left: 1.2rem;
                margin-right: 1.2rem;
            }

            .nav-item {
                margin-left: 0.65rem;
                margin-right: 0.65rem;
            }

            .mobile-overlay {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.42);
                backdrop-filter: blur(6px);
                opacity: 0;
                pointer-events: none;
                transition: opacity var(--motion-base) var(--motion-ease);
                z-index: 40;
            }

            .mobile-overlay.open {
                opacity: 1;
                pointer-events: auto;
            }

            .topbar-shell {
                padding-left: 1rem;
                padding-right: 1rem;
                height: auto;
                min-height: 5rem;
            }

            main {
                padding: 1rem;
            }

            .mobile-toolbar {
                align-items: stretch;
            }

            .mobile-toolbar-actions {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .mobile-toolbar-actions > * {
                width: 100%;
            }

            .mobile-panel {
                padding: 1rem;
            }

            .mobile-stats {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .mobile-button-wide {
                width: 100%;
                justify-content: center;
            }

            .mobile-table {
                min-width: 680px;
            }

            .section-mobile-title {
                display: flex;
                flex-direction: column;
            }

            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_paginate {
                text-align: left !important;
                justify-content: flex-start;
            }

            .sidebar-brand {
                min-height: auto;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }

            .hover-card:hover,
            .nav-item:hover,
            main .bg-white.rounded-xl:hover,
            main .bg-white.rounded-2xl:hover,
            main .bg-white.rounded-3xl:hover {
                transform: none !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased">

    <div class="flex h-screen overflow-hidden">
        <div id="mobileOverlay" class="mobile-overlay md:hidden"></div>
        <!-- Sidebar -->
        <aside id="appSidebar" class="sidebar-shell mobile-drawer w-64 shadow-xl hidden md:flex flex-col z-10">
            <div class="sidebar-brand flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="sidebar-logo">
                        <?php if (!empty($sys['logo'])): ?>
                            <img src="uploads/<?= $sys['logo'] ?>" alt="Logo" class="h-9 w-9 object-contain">
                        <?php else: ?>
                            <i class="fas fa-mosque text-xl text-emerald-600"></i>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-display text-[1.1rem] font-semibold text-slate-800 leading-tight truncate">
                            <?= $sys['nama_aplikasi'] ?: 'SIKEP' ?>
                        </h1>
                        <p class="text-xs text-slate-500 mt-0.5 leading-4">Dashboard bendahara modern</p>
                    </div>
                </div>
                <button id="sidebarCloseButton" class="sidebar-close md:hidden" type="button" aria-label="Tutup menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-3">
                <?php foreach ($nav_groups as $group): ?>
                    <div class="nav-group">
                        <div class="nav-label"><?= $group['label'] ?></div>
                        <ul class="space-y-1">
                            <?php foreach ($group['items'] as $item): ?>
                                <li>
                                    <a href="<?= $item['href'] ?>"
                                        class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= $section_key == $item['key'] ? 'active' : '' ?>">
                                        <i class="<?= $item['icon'] ?>"></i>
                                        <span class="nav-item-text font-medium"><?= $item['text'] ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-footer p-4 border-t border-gray-100">
                <div class="sidebar-badge rounded-2xl px-4 py-3 mb-3">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em]">Akun Aktif</div>
                    <div class="mt-1 font-semibold text-sm text-slate-800"><?= htmlspecialchars($display_name) ?></div>
                    <div class="text-[11px] uppercase tracking-[0.18em] text-slate-500 mt-1"><?= str_replace('_', ' ', $user_role) ?></div>
                </div>
                <a href="logout.php?csrf_token=<?= $_SESSION['csrf_token'] ?>"
                    class="logout-link flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-2xl transition-colors">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="font-medium">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="topbar-shell h-24 flex items-center justify-between px-8 z-0">
                <div class="md:hidden flex items-center gap-3 section-mobile-title">
                    <!-- Mobile Menu Button (Hamburger) -->
                    <button id="mobileMenuButton" class="h-11 w-11 rounded-2xl bg-white text-slate-700 shadow-panel border border-slate-200/70 flex items-center justify-center focus:outline-none">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <div>
                        <div class="font-display text-base font-semibold text-slate-800"><?= $active_section['label'] ?></div>
                        <div class="text-xs text-slate-500">Area aktif saat ini</div>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-4">
                    <div class="section-chip section-glow h-12 w-12 rounded-2xl flex items-center justify-center text-lg">
                        <i class="<?= $active_section['icon'] ?>"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold text-gray-800"><?= $active_section['label'] ?></h2>
                            <span class="section-chip text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-[0.2em]">Active</span>
                        </div>
                        <p class="text-sm text-gray-500"><?= $active_section['desc'] ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-4 ml-auto">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-semibold text-slate-800">
                            <?= $display_name ?>
                        </div>
                        <div class="text-xs text-slate-500 uppercase tracking-[0.16em]">
                            <?= str_replace('_', ' ', $user_role) ?>
                        </div>
                    </div>
                    <a href="profil.php"
                        class="h-12 w-12 bg-gradient-to-br from-emerald-100 to-teal-100 text-emerald-700 rounded-2xl flex items-center justify-center font-semibold text-lg hover:from-emerald-200 hover:to-teal-200 transition-colors shadow-panel border border-white">
                        <?= strtoupper(substr($display_name, 0, 1)) ?>
                    </a>
                </div>
            </header>

            <!-- Content Body -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-transparent p-8">

<?php
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: " . base_url('login.php'));
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'];
$user_id = $_SESSION['id_user'];

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        .nav-item.active {
            background-color: rgba(16, 185, 129, 0.1);
            color: #059669;
            border-right: 3px solid #059669;
        }

        /* Custom Animations */
        @keyframes fadeInUpCustom {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUpCustom 0.6s ease-out forwards;
        }

        .stagger-1 {
            animation-delay: 0.1s;
        }

        .stagger-2 {
            animation-delay: 0.2s;
        }

        .stagger-3 {
            animation-delay: 0.3s;
        }

        .stagger-4 {
            animation-delay: 0.4s;
        }

        /* Card Hover Effect */
        .hover-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Custom SweetAlert Emerald Theme */
        .swal2-emerald-popup {
            border-radius: 15px !important;
        }

        .swal2-emerald-confirm {
            background-color: #059669 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-xl hidden md:flex flex-col z-10 transition-all duration-300">
            <div class="h-24 flex items-center px-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <?php if (!empty($sys['logo'])): ?>
                        <img src="uploads/<?= $sys['logo'] ?>" alt="Logo" class="h-10 w-10 object-contain">
                    <?php else: ?>
                        <i class="fas fa-mosque text-2xl text-emerald-600"></i>
                    <?php endif; ?>
                    <h1 class="text-xl font-bold text-gray-800 leading-tight">
                        <?= $sys['nama_aplikasi'] ?: 'SIKEP' ?>
                    </h1>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1">
                    <li>
                        <a href="index.php"
                            class="nav-item flex items-center px-6 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= $current_page == 'index.php' ? 'active' : '' ?>">
                            <i class="fas fa-home w-6"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <a href="santri.php"
                            class="nav-item flex items-center px-6 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= ($current_page == 'santri.php' || $current_page == 'santri_tambah.php' || $current_page == 'santri_edit.php') ? 'active' : '' ?>">
                            <i class="fas fa-user-graduate w-6"></i>
                            <span class="font-medium">Data Santri</span>
                        </a>
                    </li>

                    <li>
                        <a href="alumni.php"
                            class="nav-item flex items-center px-6 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= ($current_page == 'alumni.php') ? 'active' : '' ?>">
                            <i class="fas fa-user-graduate w-6"></i>
                            <span class="font-medium">Data dan Tagihan Alumni</span>
                        </a>
                    </li>

                    <li>
                        <a href="tagihan.php"
                            class="nav-item flex items-center px-6 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= ($current_page == 'tagihan.php' || $current_page == 'tagihan_buat.php' || $current_page == 'tagihan_bayar.php') ? 'active' : '' ?>">
                            <i class="fas fa-file-invoice-dollar w-6"></i>
                            <span class="font-medium">Tagihan Santri</span>
                        </a>
                    </li>

                    <li>
                        <a href="transaksi.php"
                            class="nav-item flex items-center px-6 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= $current_page == 'transaksi.php' ? 'active' : '' ?>">
                            <i class="fas fa-exchange-alt w-6"></i>
                            <span class="font-medium">Transaksi Kas</span>
                        </a>
                    </li>

                    <li>
                        <a href="laporan.php"
                            class="nav-item flex items-center px-6 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= $current_page == 'laporan.php' ? 'active' : '' ?>">
                            <i class="fas fa-file-invoice-dollar w-6"></i>
                            <span class="font-medium">Laporan</span>
                        </a>
                    </li>

                    <?php if ($user_role == 'super_admin'): ?>
                        <li>
                            <a href="users.php"
                                class="nav-item flex items-center px-6 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= $current_page == 'users.php' ? 'active' : '' ?>">
                                <i class="fas fa-users-cog w-6"></i>
                                <span class="font-medium">Manajemen User</span>
                            </a>
                        </li>
                                                <li>
                            <a href="audit_log.php"
                                class="nav-item flex items-center px-6 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= $current_page == 'audit_log.php' ? 'active' : '' ?>">
                                <i class="fas fa-history w-6"></i>
                                <span class="font-medium">Audit Log</span>
                            </a>
                        </li>
                        <li>
                            <a href="pengaturan.php"
                                class="nav-item flex items-center px-6 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= $current_page == 'pengaturan.php' ? 'active' : '' ?>">
                                <i class="fas fa-cog w-6"></i>
                                <span class="font-medium">Pengaturan Web</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="profil.php"
                            class="nav-item flex items-center px-6 py-3 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors <?= $current_page == 'profil.php' ? 'active' : '' ?>">
                            <i class="fas fa-user-circle w-6"></i>
                            <span class="font-medium">Profil Saya</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-gray-100">
                <a href="logout.php?csrf_token=<?= $_SESSION['csrf_token'] ?>"
                    class="flex items-center px-6 py-3 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="font-medium">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="h-20 bg-white shadow-sm flex items-center justify-between px-8 z-0">
                <div class="md:hidden">
                    <!-- Mobile Menu Button (Hamburger) -->
                    <button class="text-gray-600 focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>

                <div class="flex items-center gap-4 ml-auto">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-bold text-gray-800">
                            <?= $display_name ?>
                        </div>
                        <div class="text-xs text-gray-500 uppercase">
                            <?= str_replace('_', ' ', $user_role) ?>
                        </div>
                    </div>
                    <a href="profil.php"
                        class="h-10 w-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center font-bold text-xl hover:bg-emerald-200 transition-colors">
                        <?= strtoupper(substr($display_name, 0, 1)) ?>
                    </a>
                </div>
            </header>

            <!-- Content Body -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">
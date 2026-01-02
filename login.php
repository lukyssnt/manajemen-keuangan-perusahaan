<?php
require_once 'config/koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            // Set Session
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['id_unit'] = $row['id_unit']; // Null for super_admin

            header("Location: index.php");
            exit;
        }
    }
    $error = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIKEP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-pattern {
            background-color: #10b981;
            background-image: url("https://www.transparenttextures.com/patterns/arabesque.png");
            /* Arabesque pattern for Islamic feel */
        }
    </style>
</head>

<body class="bg-gray-100 h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-emerald-600 p-8 text-center bg-pattern">
            <h1 class="text-3xl font-bold text-white mb-2">SIKEP</h1>
            <p class="text-emerald-100">Sistem Informasi Keuangan Pesantren</p>
        </div>

        <div class="p-8">
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                        Username
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fas fa-user"></i>
                        </span>
                        <input
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-emerald-500 transition-colors"
                            id="username" name="username" type="text" placeholder="Username" required>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-emerald-500 transition-colors"
                            id="password" name="password" type="password" placeholder="Password" required>
                    </div>
                </div>

                <button
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105"
                    type="submit" name="login">
                    Masuk
                </button>
            </form>
        </div>
        <div class="bg-gray-50 p-4 text-center text-xs text-gray-500">
            &copy; <?= date('Y') ?> SIKEP Pesantren. All rights reserved.
        </div>
    </div>

</body>

</html>
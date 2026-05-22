<?php
require_once 'config/koneksi.php';

// Menentukan password baru secara hardcode
$new_password_raw = 'password';
$new_password_hashed = password_hash($new_password_raw, PASSWORD_DEFAULT);
$username_target = 'admin';

echo "<div style='font-family: sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>";

// Update query menggunakan Prepared Statement
$query = "UPDATE users SET password = ? WHERE username = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ss", $new_password_hashed, $username_target);

if (mysqli_stmt_execute($stmt)) {
    echo "<h2 style='color: #059669;'>✅ Reset Sukses!</h2>";
    echo "<p>Password untuk akun <b>{$username_target}</b> berhasil direset.</p>";
    echo "<p>Password baru Anda adalah: <code style='background: #eee; padding: 4px 8px; border-radius: 4px;'>{$new_password_raw}</code></p>";
    echo "<p style='color: #dc2626; font-size: 14px;'><b>PENTING:</b> Demi keamanan, harap segera hapus file <code>reset_admin.php</code> ini dari server Anda setelah digunakan!</p>";
    echo "<a href='login.php' style='display: inline-block; margin-top: 15px; padding: 10px 15px; background: #059669; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Login</a>";
} else {
    echo "<h2 style='color: #dc2626;'>❌ Reset Gagal!</h2>";
    echo "<p>Gagal mereset password: " . mysqli_error($koneksi) . "</p>";
}

echo "</div>";
?>
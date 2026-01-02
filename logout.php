<?php
require_once 'config/koneksi.php';

check_csrf_get();

session_destroy();
header("Location: login.php");
exit;
?>
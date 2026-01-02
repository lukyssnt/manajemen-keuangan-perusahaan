# SIKEP: Sistem Informasi Keuangan Pesantren 🕋💸

SIKEP adalah platform manajemen keuangan profesional yang dirancang khusus untuk kebutuhan pondok pesantren. Sistem ini mengelola unit terpisah (Putra & Putri) dengan tingkat keamanan tinggi, laporan real-time, dan transparansi yang akuntabel.

## 🚀 Fitur Unggulan (Production Ready)

- **🛡️ High Security**: Dilindungi penuh dengan sistem **CSRF Protection** di setiap form dan aksi penghapusan.
- **📜 Institutional Accountability**: Fitur **Audit Log** yang mencatat setiap aktivitas krusial pengguna (Login, Transaksi, Hapus Data) lengkap dengan IP Address.
- **📊 Real-Time Analytics**: Dashboard dinamis dengan grafik tren pendapatan & pengeluaran menggunakan Chart.js yang terhubung langsung ke database.
- **🎓 Santri Lifecycle**: Manajemen data santri aktif, sistem kenaikan kelas otomatis, hingga manajemen alumni.
- **💰 Smart Billing**: Pembuatan tagihan massal (SPP/Uang Makan) berdasarkan kelas atau unit hanya dengan beberapa klik.
- **📥 Import/Export**: Mendukung bulk import data via CSV dan export laporan ke Excel.
- **💾 Disaster Recovery**: Fitur backup database (.sql) langsung dari halaman pengaturan admin.
- **🌐 Professional URLs**: URL bersih tanpa akhiran `.php` menggunakan sistem `.htaccess`.

## 🛠️ Teknologi yang Digunakan

- **Core**: PHP 8.x
- **UI Framework**: Tailwind CSS & Vanilla CSS
- **Animations**: Animate.css
- **Database**: MySQL / MariaDB
- **Components**: Chart.js, SweetAlert2, Font Awesome 6.x

## ⚙️ Persiapan & Instalasi

1. **Clone & Database**:

   - Clone repositori ini ke folder `htdocs` Anda.
   - Buat database baru bernama `db_sikep` di phpMyAdmin.
   - Import file `database.sql` yang tersedia di folder root.

2. **Konfigurasi**:

   - Rename file `config/koneksi.php.example` menjadi `config/koneksi.php`.
   - Buka `config/koneksi.php` dan sesuaikan kredensial database Anda (host, user, password).

3. **Web Server**:
   - Pastikan modul `rewrite_module` di Apache (XAMPP) sudah aktif agar Clean URL bekerja.

## 👥 Akun Akses Default

Keamanan utama menggunakan hashing `password_hash()`. Akun default (Password: `123456`):

- **Super Admin**: `admin` (Akses Full & Audit Log)
- **Bendahara Putra**: `bendahara_putra` (Akses Unit Putra saja)
- **Bendahara Putri**: `bendahara_putri` (Akses Unit Putri saja)

## 📁 Struktur Folder Utama

```text
/config      - Inti koneksi & sistem keamanan (CSRF, Audit Log)
/layout      - Kerangka UI (Header, Sidebar, Footer)
/uploads     - Penyimpanan foto santri & nota transaksi (Protected)
/database.sql- Cetak biru struktur database terbaru
```

---

_Dikembangkan dengan ❤️ untuk kemajuan manajemen keuangan pesantren._

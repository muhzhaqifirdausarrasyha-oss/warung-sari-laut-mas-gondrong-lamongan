# Warung Sari Laut Mas Gondrong Lamongan

Website Warung Sari Laut Mas Gondrong merupakan aplikasi web untuk menampilkan informasi warung, daftar menu, kontak, serta mengelola pesanan dan pesan pelanggan.

## Teknologi
- HTML
- CSS
- JavaScript
- PHP
- MySQL
- Tailwind CSS

## Persyaratan
- XAMPP
- Web browser
- phpMyAdmin

## Cara Menjalankan Aplikasi
1. Salin folder project ke dalam direktori `htdocs` XAMPP.
2. Jalankan Apache dan MySQL melalui XAMPP Control Panel.
3. Buka phpMyAdmin melalui `http://localhost/phpmyadmin`.
4. Buat database bernama `warung-warung` jika belum tersedia.
5. Pilih database tersebut, buka menu **Import**, lalu pilih file `warung-warung.sql`.
6. Pastikan konfigurasi koneksi database pada file PHP sesuai dengan pengaturan MySQL lokal.
7. Buka website melalui `http://localhost/NAMA_FOLDER_PROJECT/`, dengan mengganti `NAMA_FOLDER_PROJECT` sesuai nama folder project.

## Halaman Aplikasi
- `index.html` — halaman utama.
- `menu.html` — daftar menu.
- `informasi.html` — informasi warung.
- `kontak.html` — halaman kontak.
- `admin.php` — login dan dashboard admin.
- `kelola-pesanan.php` — pengelolaan pesanan.
- `api.php` — pemrosesan aplikasi.

## Database
File `warung-warung.sql` disertakan dalam repository dan digunakan untuk mengimpor struktur serta data database aplikasi melalui phpMyAdmin.

## Catatan
Aplikasi menggunakan PHP dan MySQL, sehingga perlu dijalankan melalui server lokal seperti XAMPP. GitHub Pages saja tidak dapat menjalankan fitur PHP dan MySQL.
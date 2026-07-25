# RPL Klinik

Aplikasi manajemen klinik berbasis PHP dan MySQL untuk resepsionis, dokter, dan apoteker. Antarmuka menggunakan Tailwind CSS.

## Prasyarat

- XAMPP atau web server yang menyediakan **Apache**, **PHP**, dan **MySQL/MariaDB**
- Node.js dan npm (hanya diperlukan untuk membangun ulang CSS Tailwind)
- Git (opsional, untuk clone repository)

## Instalasi

### 1. Clone atau salin proyek

Jika menggunakan Git, clone repository ke folder web root XAMPP:

```bash
git clone https://github.com/HMephisto/rpl_klinik.git C:\xampp\htdocs\rpl_klinik
```

Alternatifnya, unduh repository sebagai ZIP lalu ekstrak ke folder `htdocs`.

### 2. Jalankan Apache dan MySQL

Buka **XAMPP Control Panel**, kemudian mulai layanan berikut:

- Apache
- MySQL

### 3. Buat dan isi database

1. Buka phpMyAdmin di `http://localhost/phpmyadmin`.
2. Pilih tab **Import**.
3. Pilih file `sql_klinik.sql` dari root proyek.
4. Klik **Import** / **Go**.

Skrip SQL akan membuat database `klinik`, tabel-tabel aplikasi, dan akun contoh secara otomatis.

> **Catatan:** `sql_klinik.sql` menghapus tabel lama sebelum membuatnya kembali. Jangan impor file ini pada database yang berisi data penting tanpa backup.

### 4. Periksa konfigurasi database

Konfigurasi koneksi berada di `includes/db.php`:

```php
$host = 'localhost';
$dbname = 'klinik';
$username = 'root';
$password = '';
```

Nilai di atas adalah konfigurasi default XAMPP. Sesuaikan `username` dan `password` apabila konfigurasi MySQL lokal berbeda.

### 5. Instal dependensi frontend (opsional)

File CSS hasil build sudah tersedia di `assets/css/style.css`, sehingga aplikasi dapat dijalankan tanpa langkah ini. Jalankan langkah berikut jika ingin mengubah file Tailwind pada `src/input.css`:

```bash
npm install
npm run build:css
```

Untuk membangun CSS secara otomatis saat mengedit:

```bash
npm run watch:css
```

### 6. Buka aplikasi

Akses alamat berikut melalui browser:

```text
http://localhost/rpl_klinik/login.php
```

Jika folder proyek menggunakan nama lain, ganti `rpl_klinik` pada URL sesuai nama folder tersebut.

## Akun contoh

Semua akun berikut menggunakan password: `password`

| Peran | Username |
| --- | --- |
| Resepsionis | `resepsionis` |
| Dokter Umum | `dokter1` |
| Dokter Penyakit Dalam | `dokter2` |
| Dokter Anak | `dokter3` |
| Dokter Gigi | `dokter4` |
| Dokter Mata | `dokter5` |
| Apoteker | `apoteker` |

## Struktur utama

```text
includes/          Konfigurasi database dan layout bersama
views/             Halaman berdasarkan peran pengguna
assets/css/        CSS hasil build Tailwind
src/input.css      Sumber CSS Tailwind
sql_klinik.sql     Skema database dan data awal
run_migration.php  Migrasi tambahan untuk database lama
```

## Migrasi tambahan (opsional)

`run_migration.php` hanya digunakan untuk database lama yang belum memiliki kolom `harga` pada tabel `Obat` atau status antrian terbaru. Untuk instalasi baru melalui `sql_klinik.sql`, skrip ini tidak diperlukan.

Jalankan dari browser:

```text
http://localhost/rpl_klinik/run_migration.php
```

## Troubleshooting

- **Database connection failed**: Pastikan MySQL berjalan, database `klinik` telah diimpor, dan nilai pada `includes/db.php` benar.
- **Halaman tidak ditemukan / 404**: Pastikan folder proyek berada di dalam folder `htdocs` dan URL menggunakan nama folder yang benar.
- **Tampilan CSS tidak berubah setelah edit**: Jalankan `npm run build:css` atau `npm run watch:css`.
- **Tidak dapat login**: Pastikan database sudah diimpor ulang dan gunakan salah satu akun contoh di atas.

## Catatan keamanan

Akun dan password contoh hanya untuk pengembangan lokal. Ganti password database dan akun aplikasi sebelum digunakan di lingkungan produksi.

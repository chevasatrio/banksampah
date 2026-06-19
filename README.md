<div align="center">

# SIBANK
### Sistem Informasi Bank Sampah

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Pest](https://img.shields.io/badge/Pest-F1F0E8?style=for-the-badge&logo=php&logoColor=black)](https://pestphp.com/)

**Platform Manajemen Operasional Bank Sampah Berbasis Web**

</div>

---

## 1. Ringkasan Eksekutif

**SIBANK (Sistem Informasi Bank Sampah)** adalah solusi perangkat lunak tingkat perusahaan yang dirancang khusus untuk memodernisasi dan mendigitalisasi proses operasional bank sampah. Platform ini mengotomatiskan siklus hidup manajemen limbah daur ulang, mulai dari pendaftaran nasabah, pengelolaan inventaris sampah, pencatatan transaksi (setoran dan penarikan), hingga konversi kalkulasi otomatis nilai sampah menjadi saldo tabungan finansial secara *real-time*.

Aplikasi ini dikembangkan menggunakan kerangka kerja Laravel 11, menjamin keamanan tingkat tinggi, skalabilitas, dan kemudahan dalam pemeliharaan kode ( *maintainability* ).

---

## 2. Kapabilitas Inti Sistem

- **Manajemen Data Nasabah (CRM):** Registrasi nasabah terintegrasi dengan pembuatan nomor identitas unik ( *auto-generated Member ID* ).
- **Katalog Jenis & Kategori Sampah:** Pengelolaan inventaris dengan struktur harga yang dinamis dan terklasifikasi.
- **Pencatatan Transaksi Setoran (Deposit):** Modul pencatatan setoran sampah komprehensif yang secara otomatis menghitung konversi berat (kg) menjadi ekuivalen nilai Rupiah.
- **Pencatatan Transaksi Penarikan (Withdrawal):** Sistem penarikan saldo tabungan yang dilengkapi dengan validasi saldo ketat guna mencegah *over-drafting*.
- **Dasbor Analitik & Pelaporan:** Visualisasi metrik operasional dan pembuatan laporan transaksi berdasarkan periode waktu yang dapat disesuaikan.
- **Kontrol Akses Berbasis Peran (RBAC):** Pemisahan hak akses yang jelas antara *Administrator* sistem dan *Petugas* lapangan.

---

## 3. Arsitektur & Teknologi

Sistem SIBANK dibangun menggunakan *stack* teknologi modern berikut:

- **Kerangka Kerja (Framework):** Laravel 11.x
- **Bahasa Pemrograman:** PHP 8.2+
- **Manajemen Basis Data:** MySQL / MariaDB
- **Antarmuka Pengguna (Frontend):** Laravel Blade, HTML5, CSS3, ES6 JavaScript
- **Manajemen Dependensi:** Composer & NPM
- **Infrastruktur Pengujian (QA):**
  - **Pest PHP** (Unit & Feature Testing)
  - **Laravel Dusk** (End-to-End Browser Automation)
  - **Selenium** (Automated UI Testing)

---

## 4. Prasyarat Sistem

Sebelum melakukan instalasi di lingkungan pengembangan (*development*) atau produksi (*production*), pastikan infrastruktur telah memenuhi spesifikasi minimum berikut:

- **PHP** versi 8.2 atau lebih baru (dengan ekstensi: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, XML)
- **Composer** (Package Manager untuk PHP)
- **Node.js** dan **NPM** (Untuk kompilasi aset *frontend*)
- **MySQL Server** (Atau ekuivalen seperti MariaDB via XAMPP/Laragon)

---

## 5. Panduan Instalasi & Konfigurasi

Berikut adalah langkah-langkah implementasi untuk menjalankan sistem secara lokal:

1. **Kloning Repositori**
   Unduh kode sumber melalui Git:
   ```bash
   git clone https://github.com/username/bank-sampah.git
   cd bank-sampah
   ```

2. **Instalasi Dependensi**
   Unduh pustaka yang dibutuhkan oleh sistem:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Lingkungan (*Environment*)**
   Buat salinan file konfigurasi lokal dan buat kunci enkripsi aplikasi:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Basis Data**
   Sediakan basis data kosong pada sistem MySQL Anda (misalnya: `bank_sampah`). Kemudian perbarui kredensial pada file `.env`:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bank_sampah
   DB_USERNAME=root
   DB_PASSWORD=your_secure_password
   ```

5. **Migrasi dan Inisialisasi Data Dasar (*Seeding*)**
   Bangun struktur skema basis data dan isi dengan data esensial awal:
   ```bash
   php artisan migrate --seed
   ```

6. **Kompilasi Aset Frontend**
   Bangun berkas *styling* dan skrip:
   ```bash
   npm run build
   ```

7. **Jalankan Aplikasi**
   Inisiasi server pengembangan bawaan:
   ```bash
   php artisan serve
   ```
   Aplikasi sekarang dapat diakses melalui peramban pada URL `http://localhost:8000`.

---

## 6. Jaminan Mutu & Otomatisasi Pengujian (QA Testing)

Untuk menjaga stabilitas kode dan keandalan sistem, aplikasi ini memiliki cakupan pengujian (*Test Coverage*) yang komprehensif.

### 6.1. Unit & Feature Testing (Pest PHP)
Pengujian *White-box* (validasi logika bisnis internal) dan *Black-box* (validasi titik akhir HTTP dan otorisasi) dikelola menggunakan kerangka kerja Pest. 

**Persiapan Database Pengujian:**
Pastikan terdapat basis data tersendiri bernama `sibank_db_testing` di server MySQL Anda.

**Eksekusi Test Suite:**
```bash
./vendor/bin/pest
```

### 6.2. End-to-End (E2E) Browser Testing
Pengujian UI/UX dan alur fungsionalitas end-to-end dilakukan menggunakan **Laravel Dusk** yang ditenagai oleh **Selenium ChromeDriver**.

Untuk menjalankan simulasi peramban otomatis, jalankan tiga perintah berikut pada tiga sesi terminal (CLI) yang terpisah secara paralel:

**Terminal 1 (Aktivasi Driver Selenium):**
```bash
./vendor/laravel/dusk/bin/chromedriver-win.exe --port=9515
```

**Terminal 2 (Inisiasi Web Server Khusus Dusk):**
```bash
php artisan serve
```

**Terminal 3 (Eksekusi Skenario Uji):**
```bash
# Menjalankan spesifik test case (contoh: LoginTest)
php artisan dusk tests/Browser/LoginTest.php

# Menjalankan seluruh test case browser
php artisan dusk
```

---
*Dokumen ini merupakan properti intelektual bagian dari repositori SIBANK. Diperbarui terakhir: Juni 2026.*
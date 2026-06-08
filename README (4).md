# 🗑️ SIBANK - Sistem Informasi Bank Sampah

> **Software Requirements Specification (SRS)**  
> Versi: 1.0.0 | Tanggal: Juni 2026  
> Teknologi: Laravel 11, MySQL (XAMPP), Blade, Pest, Selenium

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Deskripsi Umum Sistem](#2-deskripsi-umum-sistem)
3. [Kebutuhan Fungsional](#3-kebutuhan-fungsional)
4. [Kebutuhan Non-Fungsional](#4-kebutuhan-non-fungsional)
5. [Arsitektur Sistem](#5-arsitektur-sistem)
6. [Desain Database](#6-desain-database)
7. [Struktur Direktori Project](#7-struktur-direktori-project)
8. [Instalasi & Konfigurasi](#8-instalasi--konfigurasi)
9. [Testing dengan Pest](#9-testing-dengan-pest)
10. [Automation Testing dengan Selenium](#10-automation-testing-dengan-selenium)
11. [API Endpoint](#11-api-endpoint)
12. [Panduan Kontribusi](#12-panduan-kontribusi)

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen

Dokumen SRS (Software Requirements Specification) ini mendeskripsikan kebutuhan fungsional dan non-fungsional dari **SIBANK – Sistem Informasi Bank Sampah**. Dokumen ini ditujukan untuk pengembang, penguji (tester), dan pemangku kepentingan proyek.

### 1.2 Ruang Lingkup

SIBANK adalah aplikasi web berbasis Laravel yang dirancang untuk mengelola operasional bank sampah secara digital, meliputi:

- Pendaftaran dan manajemen nasabah
- Pencatatan setor dan tarik sampah
- Konversi sampah ke nilai rupiah (tabungan)
- Laporan transaksi dan statistik
- Manajemen jenis & harga sampah oleh admin

### 1.3 Definisi & Singkatan

| Istilah | Keterangan |
|---------|-----------|
| Bank Sampah | Unit pengelola sampah berbasis tabungan masyarakat |
| Nasabah | Warga/anggota yang terdaftar di bank sampah |
| Setor | Aktivitas menyerahkan sampah ke bank sampah |
| Tarik | Aktivitas pengambilan saldo tabungan |
| SRS | Software Requirements Specification |
| CRUD | Create, Read, Update, Delete |

### 1.4 Referensi

- Laravel 11 Documentation: https://laravel.com/docs/11.x
- Pest PHP Documentation: https://pestphp.com/docs
- Selenium PHP Documentation: https://php-webdriver.github.io
- Peraturan Menteri LH No. 13 Tahun 2012 tentang Bank Sampah

---

## 2. Deskripsi Umum Sistem

### 2.1 Perspektif Produk

SIBANK merupakan sistem mandiri berbasis web yang diakses melalui browser. Sistem ini berjalan di atas stack:

```
Browser (Client)
    └── Laravel 11 (Backend + Blade Frontend)
         └── MySQL via XAMPP (Database)
```

### 2.2 Fungsi Utama Sistem

```
SIBANK
├── Autentikasi (Login/Logout/Register)
├── Manajemen Nasabah
│   ├── Daftar Nasabah
│   ├── Profil Nasabah
│   └── Saldo Tabungan
├── Manajemen Sampah
│   ├── Kategori Sampah (Organik, Anorganik, B3)
│   ├── Jenis Sampah
│   └── Harga per KG
├── Transaksi
│   ├── Setor Sampah
│   ├── Tarik Saldo
│   └── Riwayat Transaksi
├── Laporan
│   ├── Laporan Bulanan
│   ├── Laporan Nasabah
│   └── Export PDF/Excel
└── Dashboard Admin
    ├── Statistik Harian
    ├── Total Sampah Terkumpul
    └── Total Tabungan Aktif
```

### 2.3 Karakteristik Pengguna

| Peran | Akses | Deskripsi |
|-------|-------|-----------|
| **Admin** | Penuh | Mengelola seluruh sistem, data master, laporan |
| **Petugas** | Terbatas | Input setor/tarik sampah, lihat nasabah |
| **Nasabah** | Read-only | Melihat saldo, riwayat transaksi pribadi |

### 2.4 Batasan Sistem

- Sistem hanya berjalan pada jaringan lokal (localhost) menggunakan XAMPP
- Frontend menggunakan Laravel Blade (bukan SPA/Vue/React)
- Database engine: MySQL 8.x via XAMPP
- Tidak ada integrasi payment gateway eksternal

---

## 3. Kebutuhan Fungsional

### 3.1 Modul Autentikasi

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| AUTH-01 | Sistem menyediakan halaman login dengan email dan password | Tinggi |
| AUTH-02 | Sistem melakukan validasi kredensial dan menampilkan pesan error jika salah | Tinggi |
| AUTH-03 | Sistem membuat sesi (session) setelah login berhasil | Tinggi |
| AUTH-04 | Sistem menyediakan fitur logout yang menghapus sesi | Tinggi |
| AUTH-05 | Sistem menerapkan middleware auth untuk melindungi halaman privat | Tinggi |
| AUTH-06 | Admin dapat menambah akun petugas baru | Sedang |

### 3.2 Modul Nasabah

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| NSB-01 | Admin/Petugas dapat mendaftarkan nasabah baru (nama, NIK, alamat, no HP) | Tinggi |
| NSB-02 | Sistem menghasilkan nomor anggota unik secara otomatis | Tinggi |
| NSB-03 | Admin dapat melihat daftar semua nasabah dengan fitur pencarian | Tinggi |
| NSB-04 | Admin dapat mengubah data nasabah | Sedang |
| NSB-05 | Admin dapat menonaktifkan nasabah | Sedang |
| NSB-06 | Nasabah dapat melihat profil dan saldo tabungannya | Tinggi |

### 3.3 Modul Jenis Sampah

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| SMP-01 | Admin dapat menambah kategori sampah (Organik, Anorganik, B3) | Tinggi |
| SMP-02 | Admin dapat menambah jenis sampah beserta harga per KG | Tinggi |
| SMP-03 | Admin dapat mengubah harga sampah | Tinggi |
| SMP-04 | Admin dapat menonaktifkan jenis sampah | Sedang |
| SMP-05 | Sistem menampilkan daftar harga sampah yang aktif | Tinggi |

### 3.4 Modul Transaksi Setor

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| TRS-01 | Petugas dapat mencatat transaksi setor sampah oleh nasabah | Tinggi |
| TRS-02 | Sistem menghitung otomatis nilai rupiah berdasarkan berat dan harga | Tinggi |
| TRS-03 | Sistem menambah saldo tabungan nasabah setelah setor | Tinggi |
| TRS-04 | Sistem mencetak bukti setor (slip transaksi) | Sedang |
| TRS-05 | Transaksi setor dapat memuat beberapa jenis sampah sekaligus | Tinggi |

### 3.5 Modul Transaksi Tarik

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| TRK-01 | Petugas dapat mencatat penarikan saldo nasabah | Tinggi |
| TRK-02 | Sistem memvalidasi saldo cukup sebelum penarikan | Tinggi |
| TRK-03 | Sistem mengurangi saldo tabungan nasabah setelah tarik | Tinggi |
| TRK-04 | Sistem mencetak bukti penarikan | Sedang |

### 3.6 Modul Laporan

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| LAP-01 | Admin dapat melihat laporan transaksi harian/bulanan/tahunan | Tinggi |
| LAP-02 | Admin dapat memfilter laporan berdasarkan rentang tanggal | Sedang |
| LAP-03 | Admin dapat mengekspor laporan ke format PDF | Sedang |
| LAP-04 | Dashboard menampilkan ringkasan statistik real-time | Tinggi |

---

## 4. Kebutuhan Non-Fungsional

### 4.1 Performa

- Halaman harus dimuat dalam waktu < 3 detik pada koneksi localhost
- Database query tidak boleh melebihi 500ms
- Sistem dapat menangani minimal 50 transaksi setor per hari

### 4.2 Keamanan

- Password disimpan dalam format hash (bcrypt)
- Semua input divalidasi dan di-sanitasi untuk mencegah SQL Injection & XSS
- CSRF token wajib pada setiap form POST/PUT/DELETE
- Autentikasi berbasis session dengan expire time 2 jam

### 4.3 Kegunaan (Usability)

- Antarmuka responsif, dapat diakses dari desktop dan tablet
- Navigasi intuitif dengan sidebar menu
- Notifikasi flash message untuk setiap aksi (sukses/error/warning)
- Form dilengkapi validasi sisi klien dan server

### 4.4 Pemeliharaan

- Kode mengikuti PSR-12 coding standard
- Coverage unit test minimal 80% untuk logic bisnis
- Setiap fitur memiliki minimal 1 feature test
- Migrasi database terdokumentasi dan dapat di-rollback

---

## 5. Arsitektur Sistem

### 5.1 Stack Teknologi

```
┌─────────────────────────────────────────────────┐
│                   CLIENT                        │
│         Browser (Chrome / Firefox)              │
└─────────────────────┬───────────────────────────┘
                      │ HTTP Request
┌─────────────────────▼───────────────────────────┐
│               LARAVEL 11 (PHP 8.2+)             │
│  ┌─────────────┐  ┌──────────────┐              │
│  │   Routes    │  │  Middleware  │              │
│  └──────┬──────┘  └──────┬───────┘              │
│         │                │                      │
│  ┌──────▼────────────────▼───────┐              │
│  │         Controllers           │              │
│  └──────┬───────────────┬────────┘              │
│  ┌──────▼──────┐  ┌─────▼──────┐               │
│  │   Models    │  │   Views    │               │
│  │ (Eloquent)  │  │  (Blade)   │               │
│  └──────┬──────┘  └────────────┘               │
└─────────┼───────────────────────────────────────┘
          │ Query
┌─────────▼───────────────────────────────────────┐
│              MySQL (XAMPP)                      │
│          Database: sibank_db                    │
└─────────────────────────────────────────────────┘
```

### 5.2 Pola MVC

- **Model**: Eloquent ORM, setiap tabel punya 1 model
- **View**: Blade template engine, layout dengan `@extends` dan `@section`
- **Controller**: Resource controller, satu controller per modul
- **Service Layer**: Logika bisnis dipisahkan ke `app/Services/`
- **Form Request**: Validasi dipisahkan ke `app/Http/Requests/`

---

## 6. Desain Database

### 6.1 Entity Relationship Diagram (ERD)

```
users               nasabahs               kategori_sampahs
─────────           ─────────              ────────────────
id (PK)             id (PK)                id (PK)
name                no_anggota (UNIQUE)    nama
email (UNIQUE)      nama                   deskripsi
password            nik (UNIQUE)           created_at
role (enum)         alamat                 updated_at
created_at          no_hp
updated_at          saldo
                    is_active
                    user_id (FK → users)
                    created_at
                    updated_at
                         │
                         │ 1:N
                         ▼
jenis_sampahs       transaksi_setors        detail_setor_sampahs
─────────────       ────────────────        ────────────────────
id (PK)             id (PK)                 id (PK)
nama                kode_transaksi (UNIQUE) transaksi_setor_id (FK)
harga_per_kg        nasabah_id (FK)         jenis_sampah_id (FK)
kategori_id (FK)    petugas_id (FK→users)   berat_kg
is_active           total_nilai             harga_saat_itu
created_at          created_at              subtotal
updated_at          updated_at              created_at

transaksi_tariks
────────────────
id (PK)
kode_transaksi (UNIQUE)
nasabah_id (FK → nasabahs)
petugas_id (FK → users)
jumlah
keterangan
created_at
updated_at
```

### 6.2 Skema Tabel Lengkap

#### Tabel `users`

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'petugas', 'nasabah') DEFAULT 'petugas',
    remember_token VARCHAR(100),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### Tabel `nasabahs`

```sql
CREATE TABLE nasabahs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    no_anggota VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    nik VARCHAR(16) UNIQUE NOT NULL,
    alamat TEXT NOT NULL,
    no_hp VARCHAR(15) NOT NULL,
    saldo DECIMAL(15,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### Tabel `kategori_sampahs`

```sql
CREATE TABLE kategori_sampahs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### Tabel `jenis_sampahs`

```sql
CREATE TABLE jenis_sampahs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    harga_per_kg DECIMAL(10,2) NOT NULL,
    kategori_id BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (kategori_id) REFERENCES kategori_sampahs(id)
);
```

#### Tabel `transaksi_setors`

```sql
CREATE TABLE transaksi_setors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(30) UNIQUE NOT NULL,
    nasabah_id BIGINT UNSIGNED NOT NULL,
    petugas_id BIGINT UNSIGNED NOT NULL,
    total_nilai DECIMAL(15,2) NOT NULL DEFAULT 0,
    catatan TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (nasabah_id) REFERENCES nasabahs(id),
    FOREIGN KEY (petugas_id) REFERENCES users(id)
);
```

#### Tabel `detail_setor_sampahs`

```sql
CREATE TABLE detail_setor_sampahs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaksi_setor_id BIGINT UNSIGNED NOT NULL,
    jenis_sampah_id BIGINT UNSIGNED NOT NULL,
    berat_kg DECIMAL(8,2) NOT NULL,
    harga_saat_itu DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (transaksi_setor_id) REFERENCES transaksi_setors(id) ON DELETE CASCADE,
    FOREIGN KEY (jenis_sampah_id) REFERENCES jenis_sampahs(id)
);
```

#### Tabel `transaksi_tariks`

```sql
CREATE TABLE transaksi_tariks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(30) UNIQUE NOT NULL,
    nasabah_id BIGINT UNSIGNED NOT NULL,
    petugas_id BIGINT UNSIGNED NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    keterangan TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (nasabah_id) REFERENCES nasabahs(id),
    FOREIGN KEY (petugas_id) REFERENCES users(id)
);
```

---

## 7. Struktur Direktori Project

```
bank-sampah/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── NasabahController.php
│   │   │   │   ├── JenisSampahController.php
│   │   │   │   ├── KategoriSampahController.php
│   │   │   │   ├── TransaksiSetorController.php
│   │   │   │   ├── TransaksiTarikController.php
│   │   │   │   └── LaporanController.php
│   │   │   └── Nasabah/
│   │   │       └── DashboardController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php
│   │   │   └── PetugasMiddleware.php
│   │   └── Requests/
│   │       ├── NasabahRequest.php
│   │       ├── JenisSampahRequest.php
│   │       ├── TransaksiSetorRequest.php
│   │       └── TransaksiTarikRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Nasabah.php
│   │   ├── KategoriSampah.php
│   │   ├── JenisSampah.php
│   │   ├── TransaksiSetor.php
│   │   ├── DetailSetorSampah.php
│   │   └── TransaksiTarik.php
│   └── Services/
│       ├── TransaksiSetorService.php
│       ├── TransaksiTarikService.php
│       └── LaporanService.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_nasabahs_table.php
│   │   ├── 2024_01_01_000003_create_kategori_sampahs_table.php
│   │   ├── 2024_01_01_000004_create_jenis_sampahs_table.php
│   │   ├── 2024_01_01_000005_create_transaksi_setors_table.php
│   │   ├── 2024_01_01_000006_create_detail_setor_sampahs_table.php
│   │   └── 2024_01_01_000007_create_transaksi_tariks_table.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── UserSeeder.php
│   │   ├── KategoriSampahSeeder.php
│   │   ├── JenisSampahSeeder.php
│   │   └── NasabahSeeder.php
│   └── factories/
│       ├── NasabahFactory.php
│       ├── JenisSampahFactory.php
│       └── TransaksiSetorFactory.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php         # Layout utama
│       │   ├── sidebar.blade.php     # Komponen sidebar
│       │   └── navbar.blade.php      # Komponen navbar
│       ├── auth/
│       │   └── login.blade.php
│       ├── admin/
│       │   ├── dashboard/
│       │   │   └── index.blade.php
│       │   ├── nasabah/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── show.blade.php
│       │   ├── jenis-sampah/
│       │   │   ├── index.blade.php
│       │   │   └── form.blade.php
│       │   ├── transaksi-setor/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── show.blade.php
│       │   ├── transaksi-tarik/
│       │   │   ├── index.blade.php
│       │   │   └── create.blade.php
│       │   └── laporan/
│       │       └── index.blade.php
│       └── nasabah/
│           └── dashboard/
│               └── index.blade.php
├── routes/
│   └── web.php
├── tests/
│   ├── Unit/
│   │   ├── Models/
│   │   │   ├── NasabahTest.php
│   │   │   └── JenisSampahTest.php
│   │   └── Services/
│   │       ├── TransaksiSetorServiceTest.php
│   │       └── TransaksiTarikServiceTest.php
│   ├── Feature/
│   │   ├── Auth/
│   │   │   └── LoginTest.php
│   │   ├── Nasabah/
│   │   │   └── NasabahCrudTest.php
│   │   ├── Transaksi/
│   │   │   ├── TransaksiSetorTest.php
│   │   │   └── TransaksiTarikTest.php
│   │   └── Laporan/
│   │       └── LaporanTest.php
│   └── Browser/                     # Selenium / Dusk Tests
│       ├── LoginTest.php
│       ├── NasabahTest.php
│       └── TransaksiSetorTest.php
├── .env.example
├── .env.testing
├── phpunit.xml
├── pest.config.php
└── README.md
```

---

## 8. Instalasi & Konfigurasi

### 8.1 Prasyarat

```
- PHP >= 8.2
- Composer >= 2.x
- Node.js >= 18.x
- XAMPP (Apache + MySQL)
- ChromeDriver (untuk Selenium/Dusk)
```

### 8.2 Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/username/bank-sampah.git
cd bank-sampah

# 2. Install dependensi PHP
composer install

# 3. Install dependensi Node
npm install && npm run build

# 4. Salin file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate
```

### 8.3 Konfigurasi Database (XAMPP)

Pastikan XAMPP berjalan, lalu buat database:

```sql
-- Di phpMyAdmin atau MySQL CLI
CREATE DATABASE sibank_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Edit file `.env`:

```dotenv
APP_NAME="SIBANK - Bank Sampah"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sibank_db
DB_USERNAME=root
DB_PASSWORD=
```

### 8.4 Migrasi & Seeder

```bash
# Jalankan migrasi
php artisan migrate

# Jalankan seeder
php artisan db:seed

# Atau sekaligus (fresh migration + seed)
php artisan migrate:fresh --seed
```

### 8.5 Menjalankan Server

```bash
php artisan serve
# Buka http://localhost:8000
```

### 8.6 Akun Default (dari Seeder)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@sibank.com | password |
| Petugas | petugas@sibank.com | password |

---

## 9. Testing dengan Pest

### 9.1 Setup Pest

```bash
# Install Pest
composer require pestphp/pest --dev
composer require pestphp/pest-plugin-laravel --dev

# Inisialisasi Pest
php artisan pest:install
```

### 9.2 Konfigurasi `.env.testing`

```dotenv
APP_ENV=testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sibank_db_testing
DB_USERNAME=root
DB_PASSWORD=
```

Buat database testing:

```sql
CREATE DATABASE sibank_db_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 9.3 Contoh Unit Test — Model & Service

**`tests/Unit/Services/TransaksiSetorServiceTest.php`**

```php
<?php

use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Services\TransaksiSetorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('TransaksiSetorService', function () {

    beforeEach(function () {
        $this->service = new TransaksiSetorService();
    });

    it('dapat menghitung total nilai setor dengan benar', function () {
        $items = [
            ['berat_kg' => 2.5, 'harga_per_kg' => 1000],
            ['berat_kg' => 1.0, 'harga_per_kg' => 2000],
        ];

        $total = $this->service->hitungTotal($items);

        expect($total)->toBe(4500.0);
    });

    it('menambah saldo nasabah setelah setor berhasil', function () {
        $nasabah = Nasabah::factory()->create(['saldo' => 5000]);
        $jenisSampah = JenisSampah::factory()->create(['harga_per_kg' => 2000]);

        $this->service->prosesSetor($nasabah, [
            ['jenis_sampah_id' => $jenisSampah->id, 'berat_kg' => 2]
        ]);

        expect($nasabah->fresh()->saldo)->toBe(9000.0);
    });

    it('menghasilkan kode transaksi unik', function () {
        $kode1 = $this->service->generateKodeTransaksi();
        $kode2 = $this->service->generateKodeTransaksi();

        expect($kode1)->not->toBe($kode2);
        expect($kode1)->toMatch('/^STR-\d{8}-\w+$/');
    });
});
```

**`tests/Unit/Models/NasabahTest.php`**

```php
<?php

use App\Models\Nasabah;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Nasabah Model', function () {

    it('memiliki atribut yang dapat diisi (fillable)', function () {
        $nasabah = new Nasabah();
        expect($nasabah->getFillable())->toContain('nama', 'nik', 'no_hp', 'alamat');
    });

    it('saldo default adalah nol', function () {
        $nasabah = Nasabah::factory()->create();
        expect($nasabah->saldo)->toBe(0.0);
    });

    it('no_anggota dibuat secara otomatis dan unik', function () {
        $nasabah1 = Nasabah::factory()->create();
        $nasabah2 = Nasabah::factory()->create();

        expect($nasabah1->no_anggota)->not->toBe($nasabah2->no_anggota);
        expect($nasabah1->no_anggota)->toMatch('/^NSB-\d+$/');
    });
});
```

### 9.4 Contoh Feature Test

**`tests/Feature/Auth/LoginTest.php`**

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Autentikasi', function () {

    it('menampilkan halaman login', function () {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Login');
    });

    it('admin dapat login dengan kredensial yang benar', function () {
        $user = User::factory()->create([
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@sibank.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    });

    it('menolak login dengan password salah', function () {
        User::factory()->create(['email' => 'admin@sibank.com']);

        $response = $this->post('/login', [
            'email' => 'admin@sibank.com',
            'password' => 'salah123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('pengguna tidak dapat mengakses dashboard tanpa login', function () {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    });
});
```

**`tests/Feature/Nasabah/NasabahCrudTest.php`**

```php
<?php

use App\Models\User;
use App\Models\Nasabah;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Manajemen Nasabah', function () {

    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    });

    it('admin dapat melihat daftar nasabah', function () {
        Nasabah::factory()->count(3)->create();

        $response = $this->get('/admin/nasabah');
        $response->assertStatus(200);
        $response->assertViewIs('admin.nasabah.index');
        $response->assertViewHas('nasabahs');
    });

    it('admin dapat mendaftarkan nasabah baru', function () {
        $data = [
            'nama' => 'Budi Santoso',
            'nik' => '3578011234567890',
            'alamat' => 'Jl. Mawar No. 5, Surabaya',
            'no_hp' => '081234567890',
        ];

        $response = $this->post('/admin/nasabah', $data);

        $response->assertRedirect('/admin/nasabah');
        $this->assertDatabaseHas('nasabahs', ['nik' => '3578011234567890']);
    });

    it('validasi menolak NIK yang duplikat', function () {
        Nasabah::factory()->create(['nik' => '3578011234567890']);

        $response = $this->post('/admin/nasabah', [
            'nama' => 'Nama Lain',
            'nik' => '3578011234567890',
            'alamat' => 'Alamat Lain',
            'no_hp' => '089999999999',
        ]);

        $response->assertSessionHasErrors('nik');
    });

    it('admin dapat mengubah data nasabah', function () {
        $nasabah = Nasabah::factory()->create();

        $response = $this->put("/admin/nasabah/{$nasabah->id}", [
            'nama' => 'Nama Diubah',
            'nik' => $nasabah->nik,
            'alamat' => $nasabah->alamat,
            'no_hp' => $nasabah->no_hp,
        ]);

        $response->assertRedirect('/admin/nasabah');
        $this->assertDatabaseHas('nasabahs', ['nama' => 'Nama Diubah']);
    });
});
```

**`tests/Feature/Transaksi/TransaksiSetorTest.php`**

```php
<?php

use App\Models\User;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Transaksi Setor Sampah', function () {

    beforeEach(function () {
        $this->petugas = User::factory()->create(['role' => 'petugas']);
        $this->nasabah = Nasabah::factory()->create(['saldo' => 0]);
        $this->jenisSampah = JenisSampah::factory()->create([
            'nama' => 'Plastik PET',
            'harga_per_kg' => 2000,
            'is_active' => true,
        ]);
        $this->actingAs($this->petugas);
    });

    it('petugas dapat mencatat setor sampah', function () {
        $response = $this->post('/admin/transaksi-setor', [
            'nasabah_id' => $this->nasabah->id,
            'items' => [
                [
                    'jenis_sampah_id' => $this->jenisSampah->id,
                    'berat_kg' => 3,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transaksi_setors', [
            'nasabah_id' => $this->nasabah->id,
        ]);
    });

    it('saldo nasabah bertambah setelah setor', function () {
        $this->post('/admin/transaksi-setor', [
            'nasabah_id' => $this->nasabah->id,
            'items' => [
                ['jenis_sampah_id' => $this->jenisSampah->id, 'berat_kg' => 5],
            ],
        ]);

        // 5 kg × Rp2.000 = Rp10.000
        expect($this->nasabah->fresh()->saldo)->toBe(10000.0);
    });
});
```

### 9.5 Menjalankan Tests

```bash
# Jalankan semua test
php artisan test

# Jalankan dengan Pest
./vendor/bin/pest

# Jalankan hanya unit test
./vendor/bin/pest tests/Unit

# Jalankan hanya feature test
./vendor/bin/pest tests/Feature

# Tampilkan coverage
./vendor/bin/pest --coverage

# Filter test berdasarkan nama
./vendor/bin/pest --filter="login"

# Jalankan test secara parallel
./vendor/bin/pest --parallel
```

---

## 10. Automation Testing dengan Selenium

### 10.1 Setup Laravel Dusk (Selenium-based)

```bash
# Install Laravel Dusk
composer require laravel/dusk --dev

# Install Dusk
php artisan dusk:install

# Install ChromeDriver sesuai versi Chrome
php artisan dusk:chrome-driver --detect
```

### 10.2 Konfigurasi `.env.dusk.local`

```dotenv
APP_URL=http://localhost:8000
DB_DATABASE=sibank_db_dusk
```

### 10.3 Contoh Browser Test — Login

**`tests/Browser/LoginTest.php`**

```php
<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_dapat_login_via_browser()
    {
        $user = User::factory()->create([
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                    ->assertSee('Login')
                    ->type('email', 'admin@sibank.com')
                    ->type('password', 'password')
                    ->press('Login')
                    ->assertPathIs('/admin/dashboard')
                    ->assertSee('Dashboard');
        });
    }

    /** @test */
    public function user_gagal_login_dengan_password_salah()
    {
        User::factory()->create(['email' => 'admin@sibank.com']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'admin@sibank.com')
                    ->type('password', 'password_salah')
                    ->press('Login')
                    ->assertPathIs('/login')
                    ->assertSee('These credentials do not match');
        });
    }
}
```

### 10.4 Contoh Browser Test — Manajemen Nasabah

**`tests/Browser/NasabahTest.php`**

```php
<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Nasabah;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class NasabahTest extends DuskTestCase
{
    use DatabaseMigrations;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function admin_dapat_melihat_halaman_daftar_nasabah()
    {
        Nasabah::factory()->count(3)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/admin/nasabah')
                    ->assertSee('Daftar Nasabah')
                    ->assertPresent('table')
                    ->assertPresent('.nasabah-row');
        });
    }

    /** @test */
    public function admin_dapat_menambah_nasabah_baru_via_form()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/admin/nasabah/create')
                    ->assertSee('Tambah Nasabah')
                    ->type('nama', 'Siti Rahayu')
                    ->type('nik', '3578015005800001')
                    ->type('alamat', 'Jl. Kenanga No. 12, Surabaya')
                    ->type('no_hp', '081298765432')
                    ->press('Simpan')
                    ->assertPathIs('/admin/nasabah')
                    ->assertSee('Nasabah berhasil ditambahkan')
                    ->assertSee('Siti Rahayu');
        });
    }

    /** @test */
    public function fitur_pencarian_nasabah_berfungsi()
    {
        Nasabah::factory()->create(['nama' => 'Ahmad Yani']);
        Nasabah::factory()->create(['nama' => 'Budi Pekerti']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/admin/nasabah')
                    ->type('#search', 'Ahmad')
                    ->waitFor('.nasabah-row')
                    ->assertSee('Ahmad Yani')
                    ->assertDontSee('Budi Pekerti');
        });
    }
}
```

### 10.5 Contoh Browser Test — Transaksi Setor

**`tests/Browser/TransaksiSetorTest.php`**

```php
<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TransaksiSetorTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function petugas_dapat_mencatat_transaksi_setor()
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['nama' => 'Dewi Sartika']);
        $kategori = KategoriSampah::factory()->create(['nama' => 'Anorganik']);
        $jenis = JenisSampah::factory()->create([
            'nama' => 'Botol Plastik',
            'harga_per_kg' => 1500,
            'kategori_id' => $kategori->id,
        ]);

        $this->browse(function (Browser $browser) use ($petugas, $nasabah, $jenis) {
            $browser->loginAs($petugas)
                    ->visit('/admin/transaksi-setor/create')
                    ->assertSee('Setor Sampah')
                    ->select('nasabah_id', $nasabah->id)
                    ->select('items[0][jenis_sampah_id]', $jenis->id)
                    ->type('items[0][berat_kg]', '4')
                    ->press('Simpan Transaksi')
                    ->assertSee('Transaksi berhasil dicatat')
                    ->assertSee('Rp 6.000');
        });
    }
}
```

### 10.6 Menjalankan Browser Tests

```bash
# Pastikan server berjalan di terminal terpisah
php artisan serve

# Jalankan semua Dusk test
php artisan dusk

# Jalankan test tertentu
php artisan dusk tests/Browser/LoginTest.php

# Jalankan dengan screenshot on failure (default)
php artisan dusk --without-tty

# Headless (tanpa tampilan browser)
# Set di DuskTestCase.php:
# ChromeOptions headless mode
```

---

## 11. API Endpoint

### 11.1 Daftar Route Web

| Method | URI | Controller | Middleware | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/login` | Auth\LoginController@showForm | guest | Form login |
| POST | `/login` | Auth\LoginController@login | guest | Proses login |
| POST | `/logout` | Auth\LoginController@logout | auth | Logout |
| GET | `/admin/dashboard` | Admin\DashboardController@index | auth, admin | Dashboard admin |
| GET | `/admin/nasabah` | Admin\NasabahController@index | auth | Daftar nasabah |
| GET | `/admin/nasabah/create` | Admin\NasabahController@create | auth | Form tambah nasabah |
| POST | `/admin/nasabah` | Admin\NasabahController@store | auth | Simpan nasabah |
| GET | `/admin/nasabah/{id}` | Admin\NasabahController@show | auth | Detail nasabah |
| GET | `/admin/nasabah/{id}/edit` | Admin\NasabahController@edit | auth | Form edit nasabah |
| PUT | `/admin/nasabah/{id}` | Admin\NasabahController@update | auth | Update nasabah |
| GET | `/admin/jenis-sampah` | Admin\JenisSampahController@index | auth, admin | Daftar jenis sampah |
| POST | `/admin/jenis-sampah` | Admin\JenisSampahController@store | auth, admin | Simpan jenis sampah |
| GET | `/admin/transaksi-setor` | Admin\TransaksiSetorController@index | auth | Daftar setor |
| GET | `/admin/transaksi-setor/create` | Admin\TransaksiSetorController@create | auth | Form setor |
| POST | `/admin/transaksi-setor` | Admin\TransaksiSetorController@store | auth | Proses setor |
| GET | `/admin/transaksi-tarik` | Admin\TransaksiTarikController@index | auth | Daftar tarik |
| POST | `/admin/transaksi-tarik` | Admin\TransaksiTarikController@store | auth | Proses tarik |
| GET | `/admin/laporan` | Admin\LaporanController@index | auth, admin | Laporan |
| GET | `/nasabah/dashboard` | Nasabah\DashboardController@index | auth | Dashboard nasabah |

---

## 12. Panduan Kontribusi

### 12.1 Branching Strategy

```
main          → Production-ready code
develop       → Integration branch
feature/*     → Fitur baru
bugfix/*      → Perbaikan bug
test/*        → Penambahan test
```

### 12.2 Cara Menambah Fitur

1. Buat branch baru dari `develop`
2. Tambahkan migrasi jika diperlukan
3. Buat/update Model + Relationship
4. Buat Controller + Form Request
5. Buat View Blade
6. **Wajib** tambahkan test di `tests/Feature/` dan `tests/Unit/`
7. Jalankan `./vendor/bin/pest` – semua test harus hijau
8. Buat Pull Request ke `develop`

### 12.3 Standar Penulisan Test

```php
// ✅ BENAR: Deskriptif dan informatif
it('menolak penarikan jika saldo tidak mencukupi', function () { ... });
it('saldo nasabah bertambah setelah setor berhasil', function () { ... });

// ❌ SALAH: Tidak jelas
it('test tarik', function () { ... });
it('works', function () { ... });
```

### 12.4 Checklist Sebelum PR

- [ ] Semua test Pest lulus (`./vendor/bin/pest`)
- [ ] Tidak ada N+1 query (gunakan `with()` eager loading)
- [ ] Form Request digunakan untuk validasi
- [ ] Flash message ditampilkan untuk setiap aksi
- [ ] Tidak ada `dd()` atau `dump()` yang tertinggal
- [ ] Migrasi dapat di-rollback

---

## Lisensi

Proyek ini menggunakan lisensi **MIT**. Bebas digunakan untuk keperluan pendidikan dan pengembangan bank sampah komunitas.

---

*Dibuat dengan ❤️ untuk pengelolaan sampah yang lebih baik di Indonesia*

# ♻️ RongsokKu

**Marketplace kiloan barang bekas yang menghubungkan warga dengan pengepul terdekat.**
Harga transparan, barang dijemput ke rumah, dibayar tunai di tempat.

Proyek mata kuliah **Framework Pemrograman Web** — Informatika 5B, Universitas Singaperbangsa Karawang.

---

## Latar Belakang

Warga punya rongsok menumpuk di rumah, tapi tidak tahu harga wajarnya berapa. Mereka menunggu pemulung lewat, lalu menerima harga apa pun yang ditawarkan karena tidak punya pembanding. Di sisi lain, pengepul kesulitan mendapat pasokan tetap dan masih mencatat transaksi dengan bon kertas.

Hasil validasi pasar kami terhadap **10 warga dan 2 pengepul** di Karawang:

| Temuan | Angka |
|---|---|
| Warga yang mengeluhkan harga rongsok tidak transparan | **100%** |
| Warga yang tertarik memakai solusi berbentuk platform | **90%** |
| Pengepul yang masih mencatat transaksi secara manual | **2 dari 2** |

RongsokKu menjawab masalah itu dengan satu hal: **membuat harga terlihat.**

---

## Fitur Utama

### Untuk Warga
- **Cari pengepul terdekat** — diurutkan berdasarkan jarak, rating, atau skor kepatuhan harga
- **Bandingkan harga** antar pengepul sebelum memilih
- **Ajukan penjemputan** — harga dikunci saat pengajuan dan tidak bisa diubah sepihak
- **Konfirmasi hasil timbangan** sebelum transaksi dianggap selesai, atau ajukan sengketa bila tidak sesuai
- **Dampak lingkungan & lencana** — pantau berapa kg rongsok yang sudah diselamatkan dari TPA

### Untuk Pengepul
- **Tentukan harga beli sendiri** per kategori, dibandingkan langsung dengan indeks pasar
- **Terima permintaan** dari warga yang memilih lapak, atau klaim permintaan terbuka
- **Catat transaksi otomatis** — tidak perlu bon kertas lagi
- **Bangun reputasi** lewat rating warga dan metrik performa objektif

### Untuk Admin
- Verifikasi identitas pengepul dan pengisian saldo
- Kelola kategori sampah, harga acuan resmi, dan pengaturan platform
- Pantau transaksi, tangani sengketa, dan awasi pengepul dengan kepatuhan harga rendah

---

## Yang Membuat RongsokKu Berbeda

### 📈 Indeks Harga dari Transaksi Nyata
Belum ada lembaga resmi yang menerbitkan harga rongsok secara berkala dan terbuka. Karena itu RongsokKu menyusun **Indeks RongsokKu** sendiri: nilai tengah (median) harga seluruh transaksi selesai dalam 30 hari terakhir. Indeks dilengkapi **harga acuan resmi** dari sumber lokal (bank sampah induk, pabrik daur ulang) yang setiap angkanya dicantumkan asal-usulnya.

### ⚖️ Skor Kepatuhan Harga
Rating bintang mudah dipengaruhi rasa sungkan. Maka RongsokKu menghitung metrik objektif langsung dari data: **berapa persen transaksi yang dibayar sesuai atau di atas harga yang dipajang pengepul**. Angka ini mendeteksi praktik "harga umpan" — memasang harga tinggi di aplikasi lalu menawar turun saat di lokasi.

### 💵 Uang Tidak Pernah Melewati Platform
Warga dibayar tunai langsung oleh pengepul. RongsokKu hanya mencatat transaksi dan memotong komisi dari saldo prabayar pengepul. Tidak ada dana warga yang ditahan atau disalurkan.

### ⚠️ Penanganan Limbah B3
Baterai bekas dan sebagian elektronik ditandai sebagai limbah Bahan Berbahaya dan Beracun, disertai peringatan penanganan, dan hanya bisa diterima pengepul yang berizin.

---

## Model Bisnis

Gratis untuk warga. Pengepul membayar **komisi 5%** dari nilai transaksi yang selesai, dipotong dari saldo prabayar.

```
Warga menjual 8,3 kg kardus @ Rp 2.000/kg
  Total dibayar tunai ke warga   = Rp 16.600
  Komisi platform 5%             = Rp    830  ← dipotong dari saldo pengepul
```

Pengepul dengan saldo di bawah batas minimum tidak dapat menerima permintaan baru sampai saldo diisi kembali.

---

## Alur Transaksi

```
DIAJUKAN ──► DIJADWALKAN ──► DIJEMPUT ──► MENUNGGU KONFIRMASI ──► SELESAI
    │              │                              │                  ★ komisi dipotong
    ├─► DITOLAK    └─► DIBATALKAN                 └─► SENGKETA ──► ditangani admin
    └─► DIBATALKAN
```

---

## Teknologi

| | |
|---|---|
| Framework | Laravel 12 |
| Bahasa | PHP 8.2 |
| Database | MySQL / MariaDB (XAMPP) |
| Tampilan | Blade + Tailwind CSS 4 |
| Build | Vite 7 |
| Peta | OpenStreetMap (embed, tanpa API key) |

Seluruh aplikasi dibangun **tanpa satu baris JavaScript pun.** Menu, penyaring, mode gelap, akordeon, hingga grafik tren harga dikerjakan di sisi server atau dengan HTML, CSS, dan SVG murni.

---

## Cara Menjalankan

### Prasyarat
- PHP 8.2 atau lebih baru
- Composer
- Node.js 20 atau lebih baru
- XAMPP (MySQL/MariaDB)

### Langkah Instalasi

```bash
# 1. Klon repositori
git clone https://github.com/USERNAME/NAMA-REPO.git
cd NAMA-REPO

# 2. Pasang dependensi
composer install
npm install

# 3. Siapkan berkas lingkungan
cp .env.example .env
php artisan key:generate
```

Buka `.env`, lalu sesuaikan bagian database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rongsokku
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `rongsokku` lewat phpMyAdmin, lalu lanjutkan:

```bash
# 4. Bangun tabel dan isi data demo
php artisan migrate:fresh --seed

# 5. Tautkan folder unggahan
php artisan storage:link

# 6. Build tampilan
npm run build

# 7. Jalankan
php artisan serve
```

Buka **http://127.0.0.1:8000**

### Akun Demo

Kata sandi semua akun: `password`

| Peran | Email |
|---|---|
| Warga | `andhika@warga.test` |
| Pengepul | `jaya@pengepul.test` |
| Admin | `admin@rongsokku.test` |

---

## Struktur Proyek

```
app/
├── Enums/          Status transaksi, peran, verifikasi, dll.
├── Http/
│   ├── Controllers/ Publik/ Auth/ Warga/ Pengepul/ Admin/
│   └── Middleware/  Hak akses per peran, cek verifikasi & saldo
├── Models/         16 model
├── Services/       Logika bisnis (indeks harga, metrik, pencarian)
└── Support/        Haversine, format rupiah, embed peta

database/
├── migrations/     16 tabel
└── seeders/        Data demo wilayah Karawang + 420 transaksi

resources/views/
├── components/     Sistem desain (kartu, tombol, grafik SVG, peta, dll.)
├── publik/         Beranda, pusat harga, cari pengepul, peringkat
├── auth/           Masuk & daftar
├── warga/          Dashboard warga
├── pengepul/       Dashboard pengepul
└── admin/          Dashboard admin
```

---

## Tim Pengembang

| NIM | Nama | Tanggung Jawab |
|---|---|---|
| 2410631170039 | Muhammad Rizky Rajabi | Modul Warga |
| 2410631170066 | Defry Ananta Perangin Angin | Modul Pengepul |
| 2410631170068 | Diego Andreas Simanjuntak | Modul Admin & Indeks Harga |
| 2410631170129 | Andhika Eka Pratama | Arsitektur & Sistem Desain |

Informatika 5B · Fakultas Ilmu Komputer · Universitas Singaperbangsa Karawang

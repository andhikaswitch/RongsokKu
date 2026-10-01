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

## Cara Menjalankan di Komputer Sendiri (localhost)

Panduan ini untuk **Windows + XAMPP**. Cukup dilakukan sekali; untuk pemakaian
sehari-hari lihat bagian [Menjalankan lagi](#menjalankan-lagi-sehari-hari).

### Langkah 0 — Pasang perangkat yang dibutuhkan

| Perangkat | Versi | Unduh | Cek di terminal |
|---|---|---|---|
| XAMPP (PHP + MySQL) | PHP **8.2** atau lebih baru | [apachefriends.org](https://www.apachefriends.org) | `php -v` |
| Composer | 2.x | [getcomposer.org](https://getcomposer.org/download/) | `composer -V` |
| Node.js | **20.19+** atau **22 LTS** | [nodejs.org](https://nodejs.org) | `node -v` |
| Git | terbaru | [git-scm.com](https://git-scm.com) | `git --version` |

> Kalau `php -v` bilang *"not recognized"*, tambahkan `C:\xampp\php` ke **PATH**
> Windows, lalu tutup dan buka lagi terminalnya.

### Langkah 1 — Nyalakan MySQL

Buka **XAMPP Control Panel**, tekan **Start** pada baris **MySQL** sampai
tulisannya hijau. Apache tidak perlu dinyalakan.

### Langkah 2 — Buat database

Buka [http://localhost/phpmyadmin](http://localhost/phpmyadmin) (untuk ini Apache
perlu Start sebentar), pilih tab **Databases**, isi nama `rongsokku`, pilih
collation `utf8mb4_unicode_ci`, lalu tekan **Create**.

Atau lewat terminal, tanpa phpMyAdmin:

```bash
C:\xampp\mysql\bin\mysql -u root -e "CREATE DATABASE rongsokku CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

### Langkah 3 — Ambil kode proyek

Buka terminal (Command Prompt, PowerShell, atau terminal VS Code) di folder
tempat kamu ingin menyimpan proyek, misalnya `C:\xampp\htdocs`:

```bash
git clone https://github.com/andhikaswitch/RongsokKu.git
cd RongsokKu
```

### Langkah 4 — Pasang dependensi

```bash
composer install
npm install
```

Keduanya butuh internet dan bisa makan waktu beberapa menit.

### Langkah 5 — Siapkan berkas `.env`

Salin `.env.example` menjadi `.env`. Pilih salah satu sesuai terminalmu:

```bash
copy .env.example .env      # Command Prompt
cp .env.example .env        # PowerShell / Git Bash
```

Lalu buat kunci aplikasi:

```bash
php artisan key:generate
```

**Wajib: buka berkas `.env` (bukan `.env.example`) dan pastikan bagian
database persis seperti ini.** Kalau belum, ubah lalu simpan:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rongsokku
DB_USERNAME=root
DB_PASSWORD=
```

> ⚠️ Kalau `DB_CONNECTION` masih `sqlite`, langkah 6 akan **terlihat berhasil**
> padahal datanya masuk ke berkas `database/database.sqlite`, bukan ke MySQL.
> Akibatnya database `rongsokku` di phpMyAdmin tetap kosong dan halaman
> Cari Pengepul error. Ini terjadi pada `.env.example` versi lama (sebelum
> 30 September 2026), jadi tetap periksa walaupun baru clone.
>
> Kalau MySQL-mu memakai kata sandi, isi `DB_PASSWORD`. Yang diubah selalu
> `.env`; berkas `.env.example` hanya contoh dan tidak dibaca aplikasi.

### Langkah 6 — Bangun tabel dan data demo

```bash
php artisan config:clear
php artisan migrate:fresh --seed
```

Tunggu sekitar 20–30 detik sampai muncul tabel **Akun demo**. Perintah ini
mengisi 420 transaksi contoh, indeks harga, dan antrean admin.

Lalu **pastikan datanya benar-benar masuk ke MySQL**: buka phpMyAdmin →
database `rongsokku`. Harus ada sekitar 25 tabel, dan tabel `users` berisi
sekitar 16 baris. Kalau kosong, kembali ke Langkah 5 dan periksa `.env`.

> Kalau di langkah ini muncul pertanyaan *"The SQLite database does not exist.
> Would you like to create it?"*, jawab **no**. Itu tanda `.env` masih
> memakai SQLite.

### Langkah 7 — Tautkan folder unggahan

```bash
php artisan storage:link
```

Tanpa langkah ini, foto lapak dan foto profil tidak akan tampil.

### Langkah 8 — Build tampilan

```bash
npm run build
```

### Langkah 9 — Jalankan

```bash
php artisan serve
```

Buka **[http://127.0.0.1:8000](http://127.0.0.1:8000)** di browser, lalu masuk
dengan salah satu akun demo di bawah. Biarkan terminal tetap terbuka selama
aplikasi dipakai; tekan `Ctrl + C` untuk menghentikan.

### Menjalankan lagi (sehari-hari)

Setelah instalasi pertama, cukup:

1. Start **MySQL** di XAMPP Control Panel
2. `php artisan serve`
3. Buka http://127.0.0.1:8000

### Setelah menarik perubahan dari rekan (`git pull`)

```bash
git pull
composer install
npm install
php artisan migrate:fresh --seed
npm run build
```

> ⚠️ `migrate:fresh` **menghapus seluruh isi database** lalu mengisinya ulang
> dengan data demo. Untuk proyek ini aman, karena semua datanya memang data demo.

### Kalau muncul masalah

| Pesan / gejala | Penyebab | Solusi |
|---|---|---|
| `No connection could be made because the target machine actively refused it` | MySQL belum jalan | Start MySQL di XAMPP Control Panel |
| `Unknown database 'rongsokku'` | Database belum dibuat | Ulangi Langkah 2 |
| `migrate:fresh --seed` berhasil, tapi database `rongsokku` di phpMyAdmin kosong | `.env` masih `DB_CONNECTION=sqlite` | Ubah `.env` sesuai Langkah 5, lalu `php artisan config:clear` dan `php artisan migrate:fresh --seed` |
| Halaman Cari Pengepul error (`no such function: ASIN` / `RADIANS`) | Aplikasi berjalan di SQLite, bukan MySQL | Sama seperti baris di atas |
| `Vite manifest not found` | Tampilan belum di-build | `npm run build` |
| `No application encryption key has been specified` | Lupa membuat kunci | `php artisan key:generate` |
| `Your requirements could not be resolved` saat `composer install` | Versi PHP terlalu lama | Pakai XAMPP dengan PHP 8.2+ |
| Foto lapak / profil tidak muncul | Tautan storage belum dibuat | `php artisan storage:link` |
| `Failed to listen on 127.0.0.1:8000` | Port sudah dipakai | `php artisan serve --port=8001` |
| Tampilan berantakan / tanpa warna | CSS lama | `npm run build`, lalu `Ctrl + F5` di browser |
| Pesan galat berbahasa Inggris | `.env` lama | Pastikan `APP_LOCALE=id` di `.env` |

### Akun Demo

Kata sandi semua akun: `password`

| Peran | Email |
|---|---|
| Warga | `andhika@warga.test` |
| Pengepul | `jaya@pengepul.test` |
| Pengepul (belum terverifikasi) | `maju@pengepul.test` |
| Admin | `admin@rongsokku.test` |

---

### Pengujian & Tugas Terjadwal

```bash
php artisan test               # 19 pengujian otomatis alur bisnis
php artisan schedule:work      # hitung indeks harga & metrik pengepul tiap malam
```

Data demo sudah berisi satu contoh di setiap antrean admin — pengepul menunggu
verifikasi, top-up menunggu konfirmasi, dan satu sengketa — sehingga seluruh fitur
bisa langsung dicoba.

---

## Struktur Proyek

```
app/
├── Enums/          Status transaksi, peran, verifikasi, dll.
├── Http/
│   ├── Controllers/ Publik/ Auth/ Warga/ Pengepul/ Admin/
│   ├── Middleware/  Hak akses per peran, cek verifikasi & saldo
│   └── Requests/    Validasi form dengan pesan bahasa Indonesia
├── Models/         16 model
├── Policies/       Hak akses per permintaan jemput
├── Services/       Seluruh logika bisnis (transaksi, saldo, komisi, sengketa, indeks)
└── Support/        Haversine, format rupiah, embed peta

database/
├── migrations/     16 tabel
└── seeders/        Data demo wilayah Karawang + 420 transaksi + antrean admin

resources/views/
├── components/     Sistem desain (kartu, tombol, modal, grafik SVG, peta, dll.)
├── publik/         Beranda, pusat harga, cari pengepul, peringkat
├── auth/           Masuk & daftar
├── warga/          Dashboard, ajukan penjemputan, permintaan, dampak
├── pengepul/       Dashboard, harga, permintaan, terbuka, dompet, lapak
└── admin/          Verifikasi, top-up, transaksi, sengketa, kategori, harga, pengguna

docs/superpowers/specs/   Dokumen arsitektur lengkap
docs/diagram/             Diagram UML (use case, activity, sequence, class) — buka di app.diagrams.net
tests/                    Pengujian otomatis
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

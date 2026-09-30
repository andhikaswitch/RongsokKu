# CLAUDE.md — RongsokKu

Panduan wajib untuk siapa pun (manusia atau AI) yang menulis kode di proyek ini.

---

## Tentang Proyek

**RongsokKu** — marketplace kiloan barang bekas. Warga menjual rongsok, pengepul
menjemput ke rumah dan membayar tunai di tempat.

Tugas mata kuliah **Framework Pemrograman Web**, Informatika 5B,
Universitas Singaperbangsa Karawang. Dikerjakan 4 orang:

| NIM | Nama | Tanggung jawab |
|---|---|---|
| 2410631170039 | Muhammad Rizky Rajabi | Modul Warga |
| 2410631170066 | Defry Ananta Perangin Angin | Modul Pengepul |
| 2410631170068 | Diego Andreas Simanjuntak | Modul Admin + Indeks Harga |
| 2410631170129 | Andhika Eka Pratama | Fondasi + Sistem Desain |

Dasar rancangan: `docs/` (BMC + validasi pasar 10 warga & 2 pengepul).
Spesifikasi lengkap: `docs/superpowers/specs/2026-09-23-rongsokku-design.md`.

---

## ATURAN KERAS — jangan dilanggar

### 1. NOL JavaScript

Tim belum mempelajari JavaScript. **Dilarang** menulis atau memasang:

- File `.js` apa pun di `resources/js/` selain bawaan Laravel
- Tag `<script>` berisi logika
- Alpine.js, Livewire, Vue, React, jQuery, HTMX
- Atribut event inline (`onclick`, `onchange`, `onsubmit`, ...)

**Yang DIPERBOLEHKAN** karena bukan JavaScript:

- `<svg>` yang dirender Blade (grafik, ikon, sparkline)
- `<iframe>` embed OpenStreetMap
- Elemen HTML asli `<details>` / `<summary>`
- Trik CSS Tailwind: `peer`, `group`, `:target`, `checked:`, `has-[]`

Semua interaksi ditangani **server-side**: form `GET`/`POST`, redirect, session flash.
Kalau sebuah fitur mustahil tanpa JS, **ganti rancangan fiturnya** — jangan tambah JS.

### 2. Bahasa Indonesia untuk yang dilihat pengguna

- Teks UI, label, tombol, pesan validasi, pesan error → **Bahasa Indonesia**
- Nama kolom database → **Bahasa Indonesia** (`harga_per_kg`, `berat_final`)
- Nama tabel → **Bahasa Indonesia jamak** (`permintaan_jemput`, `kategori_sampah`)
- Nama kelas, metode, variabel PHP → **Bahasa Inggris** (konvensi Laravel)
- Komentar kode → Bahasa Indonesia, hanya kalau menjelaskan *kenapa*, bukan *apa*

### 3. Controller tipis, Service tebal

Controller **hanya boleh**: terima request → panggil service → return view/redirect.

Dilarang di Controller: `DB::transaction`, perhitungan komisi, perubahan status,
manipulasi saldo, query kompleks.

```php
// ✅ BENAR
public function terima(TerimaPermintaanRequest $request, PermintaanJemput $permintaan)
{
    $this->service->terima($permintaan, $request->validated());
    return back()->with('sukses', 'Permintaan berhasil diterima.');
}

// ❌ SALAH — logika bisnis bocor ke controller
public function terima(Request $request, PermintaanJemput $permintaan)
{
    DB::transaction(function () use ($permintaan) {
        $permintaan->update(['status' => 'dijadwalkan']);
        $permintaan->pengepul->decrement('saldo', $komisi);
    });
}
```

### 4. Uang tidak pernah lewat platform

Warga dibayar **tunai** oleh pengepul di lokasi. Sistem hanya **mencatat**.
Jangan pernah membuat fitur yang menahan/menyalurkan uang transaksi warga.

Saldo pengepul **hanya** dipakai untuk membayar komisi ke platform.

### 5. Komisi hanya dipotong saat status SELESAI

Tidak sebelumnya, tidak di status lain. Selalu di dalam `DB::transaction`
dengan `lockForUpdate()` pada baris profil pengepul.

### 6. Harga dibekukan saat permintaan dibuat

Saat warga mengajukan, harga pengepul disalin ke
`item_permintaan.harga_estimasi_per_satuan`. Perubahan daftar harga setelahnya
**tidak boleh** mengubah permintaan yang sedang berjalan.

### 7. Buku besar adalah sumber kebenaran

`mutasi_saldo` = sumber kebenaran. `profil_pengepul.saldo` = cache.
Setiap perubahan saldo **wajib** mencatat baris mutasi berisi
`saldo_sebelum` dan `saldo_sesudah`.

### 8. Rating murni menilai layanan

**Dilarang** mengurangi rating secara otomatis karena harga.
Perilaku harga dinilai lewat metrik objektif terpisah
(`skor_kepatuhan_harga`, `tingkat_penerimaan`, `ketepatan_waktu`).

### 9. Indeks harga butuh minimal 5 transaksi

Di bawah itu tampilkan "Data belum cukup" dan jatuhkan ke harga acuan resmi.
Gunakan **median**, bukan rata-rata — agar satu transaksi menyimpang
tidak menggeser indeks.

### 10. Tanpa layanan berbayar

Dilarang memakai Google Maps API, payment gateway berbayar, atau layanan
yang butuh kartu kredit. Peta pakai `iframe` OpenStreetMap (gratis, tanpa API key).

---

## Stack

| | |
|---|---|
| Framework | Laravel 12 |
| PHP | 8.2.12 |
| Database | MySQL / MariaDB 10.4 (XAMPP) — `rongsokku` |
| Template | Blade |
| CSS | Tailwind 4 (via `@tailwindcss/vite`) |
| Build | Vite 7 |
| Font | Plus Jakarta Sans (Google Fonts) |
| Peta | `iframe` OpenStreetMap |
| Pengujian | PHPUnit |

---

## Perintah

```bash
# Nyalakan MySQL dulu lewat XAMPP Control Panel (atau):
/c/xampp/mysql/bin/mysqld.exe --defaults-file=/c/xampp/mysql/bin/my.ini --standalone &

php artisan migrate:fresh --seed     # bangun ulang database + data demo
npm run dev                          # Vite mode pengembangan
npm run build                        # build produksi
php artisan serve                    # http://127.0.0.1:8000
php artisan test                     # jalankan pengujian
./vendor/bin/pint                    # rapikan format kode
php artisan rongsokku:hitung-indeks  # hitung ulang indeks harga
```

---

## Struktur

```
app/
├── Enums/            PeranPengguna, StatusPermintaan, StatusVerifikasi,
│                     StatusTopup, JenisMutasi, TipeSumberHarga
├── Exceptions/       AksiTidakValid — pelanggaran aturan bisnis → pesan galat
├── Http/
│   ├── Controllers/  Publik/ Auth/ Warga/ Pengepul/ Admin/ + Berkas, Profil
│   ├── Middleware/   PastikanPeran, PastikanPengepulTerverifikasi,
│   │                 PastikanSaldoCukup
│   └── Requests/     Umum/ Warga/ Pengepul/ Admin/
├── Models/
├── Policies/         PermintaanJemputPolicy
├── Services/         ★ Semua logika bisnis (lihat daftar di bawah)
├── Support/          Haversine, Format, OsmEmbed, helpers.php
└── Console/Commands/ HitungIndeksHarga, SegarkanMetrikPengepul

resources/views/
├── components/       Sistem desain — pakai ulang, jangan tulis ulang markup
│   └── layouts/      dasar · publik · auth · panel  (<x-layouts.panel>)
├── partials/         Potongan bersama, mis. rincian & linimasa permintaan
└── publik/ auth/ profil/ warga/ pengepul/ admin/

lang/id/              Pesan validasi bahasa Indonesia (APP_LOCALE=id)
tests/                Feature/ (alur bisnis) · Unit/ (perhitungan)
```

**Service yang tersedia** — cek dulu sebelum menulis logika baru:

| Service | Tanggung jawab |
|---|---|
| `PermintaanJemputService` | Seluruh perpindahan status, pembekuan harga, penyelesaian + komisi |
| `KeranjangPengajuanService` | Draf wizard pengajuan warga (disimpan di session) |
| `WalletService` | Satu-satunya pintu mengubah saldo (kredit/debit + buku besar) |
| `TopupService`, `VerifikasiService`, `SengketaService` | Antrean yang diperiksa admin |
| `HargaPengepulService`, `IndeksHargaService` | Daftar harga pengepul, indeks pasar |
| `MetrikPengepulService`, `UlasanService`, `LencanaService` | Reputasi & gamifikasi |
| `PenggunaService`, `AuditService`, `LaporanService` | Akun, log audit, laporan/ekspor CSV |
| `PengepulSearchService` | Pencarian pengepul terdekat (Haversine di SQL) |

Aturan bisnis yang dilanggar di service → lempar `AksiTidakValid('pesan')`.
Handler di `bootstrap/app.php` mengembalikan pengguna ke halaman sebelumnya
dengan pesan galat, jadi controller tidak perlu `try/catch`.

---

## Konvensi

**Sebelum membuat markup baru, cek `resources/views/components/` dulu.**
Kalau komponennya sudah ada, pakai. Kalau mirip tapi beda sedikit, tambahkan prop.

```blade
<x-kartu judul="...">...</x-kartu>
<x-tombol variant="primer" :href="route('warga.ajukan')">Ajukan Jemput</x-tombol>
<x-lencana-status :status="$permintaan->status" />
<x-harga-indeks :harga="$harga" :posisi="$h->posisiTerhadapIndeks()" />
<x-modal id="modal-batal" judul="...">...</x-modal>   {{-- buka: <a href="#modal-batal"> --}}
<x-pil-saring :opsi="[...]" />                         {{-- penyaring status/tab --}}
```

**Pernyataan `use` di Blade** wajib di baris paling atas berkas, di luar
`<x-layouts.*>`. Di dalam komponen, hasil kompilasinya berada di dalam blok
`if` sehingga PHP menolaknya:

```blade
@php
    use App\Enums\StatusPermintaan;
@endphp
<x-layouts.panel judul="...">
```

**Kolom yang hanya boleh diisi sistem** (saldo, rating, metrik) sengaja tidak
masuk `$fillable`. Isi dengan `forceFill([...])->save()`. `update([...])` akan
mengabaikannya diam-diam — ini tidak terlihat di seeder karena seeder berjalan
tanpa proteksi mass-assignment, jadi selalu uji lewat PHPUnit.

**Uang** disimpan sebagai `decimal(12,2)`, **berat** sebagai `decimal(8,2)`.
Jangan pakai `float` untuk uang.

**Enum** selalu di-cast di model, jangan bandingkan string mentah:

```php
protected function casts(): array
{
    return ['status' => StatusPermintaan::class];
}
```

**Route** dinamai per peran: `warga.permintaan.index`, `pengepul.harga.index`,
`admin.verifikasi.show`. Prefix URL pengepul adalah `/mitra`.

**Berkas pribadi** (KTP, bukti transfer, foto barang, bukti sengketa) disimpan
di disk `local`, bukan `public`, dan hanya disajikan lewat `BerkasController`
yang memeriksa hak akses. Hanya foto lapak, foto profil, dan dokumen harga
acuan yang boleh di disk `public`.

---

## Yang TIDAK perlu dibangun

Jangan tambahkan tanpa diminta: chat real-time, notifikasi push, aplikasi mobile,
pembayaran online, pencairan poin jadi uang, integrasi API pihak ketiga berbayar,
multi-bahasa, tema yang bisa dikustom pengguna.

Poin loyalitas belum dibangun (tim memilih papan peringkat & lencana).
Bila kelak ditambahkan, poin **tidak boleh bisa dicairkan jadi uang** — hanya
ditukar hadiah — untuk menghindari ranah regulasi uang elektronik.

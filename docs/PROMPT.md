# PROMPT.md — RongsokKu

Kumpulan prompt siap pakai untuk melanjutkan pembangunan RongsokKu.
Tempelkan blok yang relevan ke Claude Code, Copilot, atau asisten AI lain.

**Status: seluruh modul sudah dibangun dan diuji** (19 pengujian PHPUnit lolos).
Bagian 3 kini berfungsi sebagai spesifikasi tiap modul — rujukan saat menjelaskan
fitur di laporan atau saat mengubah perilakunya. Untuk menambah fitur baru,
pakai prompt di Bagian 4. Arsitektur lengkap: `docs/superpowers/specs/2026-09-23-rongsokku-design.md`.

---

## 0. Prompt Konteks — SELALU tempelkan ini lebih dulu

```
Saya melanjutkan proyek RongsokKu: marketplace kiloan barang bekas berbasis
Laravel 12 + Blade + Tailwind 4. Warga menjual rongsok, pengepul menjemput ke
rumah dan membayar TUNAI di lokasi.

BACA DULU file CLAUDE.md di root proyek. Isinya batasan keras yang tidak boleh
dilanggar. Ringkasannya:

1. NOL JAVASCRIPT. Dilarang menulis file .js, tag <script>, atau atribut
   onclick/onchange. Alpine, Livewire, Vue, React, jQuery, HTMX semuanya
   dilarang. Tim belum mempelajari JavaScript.
   Yang boleh: <svg> yang dirender Blade, <iframe> OpenStreetMap,
   <details>/<summary>, dan trik CSS Tailwind (peer, group, :target, checked:).
   Semua interaksi ditangani server: form GET/POST, redirect, session flash.

2. Bahasa Indonesia untuk teks UI, nama tabel, dan nama kolom.
   Nama kelas/metode/variabel PHP tetap Bahasa Inggris.

3. Controller TIPIS. Semua logika bisnis di app/Services/.
   Dilarang menaruh DB::transaction atau perhitungan komisi di Controller.

4. Uang TIDAK PERNAH lewat platform. Warga dibayar tunai oleh pengepul.
   Sistem hanya mencatat, lalu memotong komisi dari saldo pengepul.

5. Pakai ulang komponen di resources/views/components/. Jangan tulis markup
   dari nol kalau komponennya sudah ada.

Stack: Laravel 12, PHP 8.2, MySQL (XAMPP, database "rongsokku"),
Tailwind 4 via Vite, tanpa paket JavaScript apa pun.
```

---

## 1. Yang SUDAH selesai — jangan dibangun ulang

### Database (16 tabel, sudah bermigrasi)

```
wilayah                  hierarki provinsi > kabupaten > kecamatan > kelurahan
users                    + peran, telepon, wilayah_id, alamat_detail, lat/lng
kategori_sampah          2 tingkat: 6 golongan induk + 20 turunan
profil_pengepul          verifikasi, saldo, rating, metrik objektif
harga_pengepul           harga per kategori milik tiap pengepul
permintaan_jemput        transaksi inti, 8 status
item_permintaan          rincian barang, estimasi vs final
mutasi_saldo             buku besar saldo (sumber kebenaran)
permintaan_topup         pengisian saldo + bukti transfer
ulasan                   rating & komentar warga
sumber_harga_pasar       harga acuan resmi + jejak sumbernya
indeks_harga             indeks harian dari transaksi nyata
sengketa                 penanganan perselisihan
pengaturan_platform      tarif komisi, saldo minimum, rekening
lencana + lencana_pengguna
log_audit
```

### Kode yang sudah ada

```
app/Enums/          PeranPengguna, StatusPermintaan, StatusVerifikasi,
                    StatusTopup, JenisMutasi, TipeSumberHarga
app/Models/         16 model lengkap dengan relasi & scope
app/Services/       PermintaanJemputService, KeranjangPengajuanService,
                    WalletService, TopupService, VerifikasiService,
                    SengketaService, HargaPengepulService, IndeksHargaService,
                    MetrikPengepulService, UlasanService, LencanaService,
                    PenggunaService, AuditService, LaporanService,
                    PengepulSearchService
app/Support/        Haversine, OsmEmbed, Format, helpers.php
app/Http/Middleware/ PastikanPeran, PastikanPengepulTerverifikasi,
                    PastikanSaldoCukup
app/Http/Requests/  Umum/ Warga/ Pengepul/ Admin/
app/Console/Commands/ HitungIndeksHarga, SegarkanMetrikPengepul (terjadwal harian)
tests/              Feature/AlurPenjemputanTest, Feature/AdminDanKeuanganTest,
                    Unit/PerhitunganTest
```

### Komponen Blade siap pakai

```blade
<x-layouts.publik>          {{-- halaman publik + navbar + footer --}}
<x-layouts.panel judul="">  {{-- dashboard + sidebar per peran --}}
<x-layouts.auth judul="">   {{-- halaman login/daftar --}}

<x-kartu judul="" keterangan="">        <x-slot:aksi> <x-slot:kaki>
<x-tombol variant="primer|sekunder|halus|bahaya|hantu|nilai"
          ukuran="kecil|sedang|besar" href="" ikon="" ikon-kanan="" penuh>
<x-kolom label="" nama="" tipe="" wajib bantuan="" awalan="" akhiran="">
<x-pilihan label="" nama="" :opsi="[]" kosong="" wajib>
<x-statistik label="" nilai="" ikon="" warna="" keterangan="" :tren="">
<x-lencana warna="merk|emerald|amber|rose|sky|violet|slate" ikon="">
<x-lencana-status :status="$enum">
<x-bintang :nilai="" :jumlah="" ukuran="">
<x-meter label="" :nilai="" :maks="" warna="">
<x-sparkline :data="[]" tinggi="h-16">     {{-- SVG, bukan JS --}}
<x-peta :lat="" :lng="" tinggi="h-64">     {{-- iframe OSM --}}
<x-avatar :nama="" :foto="" ukuran="">
<x-kosong ikon="" judul="" pesan="">
<x-notifikasi />                           {{-- session flash --}}
<x-ikon nama="" ukuran="size-5" />         {{-- 40+ ikon SVG --}}
<x-harga-indeks :harga="" :posisi="">
<x-logo /> <x-tombol-tema />
```

Fungsi global: `rupiah()`, `berat()`, `jarak()`, `tanggal_id()`, `pengaturan()`

### Halaman yang sudah jadi

Publik: beranda, pusat harga pasar, detail kategori, cari pengepul,
detail pengepul, papan peringkat, cara kerja, tentang, FAQ.
Auth: login, daftar.

Warga: dashboard, wizard ajukan (pilih cara → barang → alamat & jadwal),
permintaan saya + rincian (batal, konfirmasi, sengketa, ulasan), dampak & lencana, profil.

Pengepul: dashboard, verifikasi + status, daftar harga, permintaan masuk + rincian
(terima, tolak, berangkat, timbang, batal), permintaan terbuka (klaim), dompet +
isi saldo, profil lapak, akun, performa & balas ulasan.

Admin: dashboard, verifikasi pengepul, verifikasi top-up, transaksi + ekspor CSV,
sengketa, kategori (CRUD), harga acuan (CRUD) & indeks (hitung ulang), pengguna
(nonaktifkan/aktifkan/atur ulang sandi), pengaturan platform, log audit.

---

## 2. Aturan bisnis yang WAJIB dipatuhi

| Aturan | Penjelasan |
|---|---|
| Komisi 5% | Hanya dipotong saat status `SELESAI`, tidak pernah sebelumnya |
| Harga dibekukan | Disalin ke `item_permintaan.harga_estimasi_per_satuan` saat permintaan dibuat |
| Saldo minimum | Pengepul di bawah `saldo_minimum` tidak bisa terima permintaan baru |
| Buku besar | Setiap perubahan saldo wajib mencatat `mutasi_saldo` + saldo sebelum/sesudah |
| Rating murni | Dilarang mengurangi rating otomatis karena harga |
| Harga bebas | Harga menyimpang hanya diberi LABEL, tidak diblokir |
| Indeks ≥ 5 transaksi | Di bawah itu tampilkan "Data belum cukup" |
| Median, bukan rata-rata | Agar tahan terhadap harga menyimpang |
| Limbah B3 | Hanya pengepul dengan `izin_b3 = true` yang boleh menerimanya |

### Alur status permintaan

```
DIAJUKAN ──tolak──► DITOLAK
    │    ──batal──► DIBATALKAN
    └──terima──► DIJADWALKAN ──berangkat──► DIJEMPUT
                     └──timbang──► MENUNGGU_KONFIRMASI
                              ├──warga setuju──► SELESAI  ★ komisi dipotong
                              └──warga tolak───► SENGKETA ──► SELESAI/DIBATALKAN
```

---

## 3. Spesifikasi per modul (✅ semua sudah dibangun)

Semua modul di bawah sudah diimplementasikan. Prompt aslinya tetap disimpan
sebagai spesifikasi; beberapa detail disesuaikan saat implementasi:

- Wizard memakai **keranjang di session** (`KeranjangPengajuanService`), bukan
  baris draf di database, agar tabel `permintaan_jemput` tidak berisi draf terbengkalai.
- Komisi dipotong di `PermintaanJemputService::selesaikan()`, dipakai bersama oleh
  konfirmasi warga dan keputusan sengketa admin.
- Sengketa punya tiga keputusan: selesaikan dengan harga awal, selesaikan sesuai
  timbangan, atau batalkan — plus opsi sanksi menonaktifkan akun pengepul.
- Log audit dicatat eksplisit lewat `AuditService` di tiap aksi admin & keuangan.

### 3.1 — Wizard Pengajuan Penjemputan (Rizky, 2410631170039)

```
Bangun wizard pengajuan penjemputan untuk warga, TANPA JavaScript.

Alur 3 langkah memakai draf yang tersimpan di database:

Langkah 1 - Pilih pengepul
  GET /warga/ajukan?pengepul={slug}
  Jika belum memilih, arahkan ke halaman cari pengepul.
  Tampilkan ringkasan pengepul: nama, jarak, rating, skor kepatuhan harga.

Langkah 2 - Tambah barang (bisa berulang)
  Buat PermintaanJemput berstatus draf begitu pengepul dipilih.
  Form: pilih kategori (hanya yang dijual pengepul ini dan sedang_menerima),
  isi estimasi berat, foto opsional.
  POST menambah satu ItemPermintaan lalu redirect kembali ke langkah 2.
  Tampilkan daftar barang yang sudah masuk + tombol hapus per baris.
  ★ PENTING: salin harga_per_satuan pengepul ke harga_estimasi_per_satuan.
    Harga dibekukan di sini.
  Kalau kategori berlabel limbah_b3, tampilkan peringatan_b3 dan tolak
  bila pengepul tidak punya izin_b3.

Langkah 3 - Alamat & jadwal
  Alamat terisi otomatis dari profil warga, masih bisa diubah.
  Pilih tanggal (mulai besok) dan sesi (pagi/siang/sore).
  Tampilkan ringkasan estimasi total.
  POST terakhir mengubah status draf menjadi DIAJUKAN.

Buat:
- App\Services\PermintaanJemputService dengan metode:
  buatDraf, tambahItem, hapusItem, kirim, batal
- App\Http\Requests\Warga\{TambahItemRequest, KirimPermintaanRequest}
- App\Policies\PermintaanJemputPolicy
- resources/views/warga/ajukan/{langkah1,langkah2,langkah3}.blade.php
- Kode permintaan format RSK-000001, dibuat dalam DB::transaction

Tambahkan juga opsi "Permintaan Terbuka": profil_pengepul_id = null,
permintaan_terbuka = true, terlihat oleh semua pengepul terverifikasi.
```

### 3.2 — Daftar Permintaan & Konfirmasi Warga (Rizky)

```
Bangun halaman permintaan warga:

GET /warga/permintaan          daftar + saring status (form GET) + paginasi
GET /warga/permintaan/{kode}   rincian + lacak status

Pada halaman rincian:
- Penanda progres 5 langkah (pakai StatusPermintaan::langkah())
- Tabel barang: kolom estimasi berdampingan dengan hasil timbangan,
  beri tanda selisihnya (pakai ItemPermintaan::selisihBeratPersen())
- Peta lokasi penjemputan (<x-peta>)
- Tombol WhatsApp ke pengepul (hanya setelah permintaan diterima)

Aksi yang tersedia:
- Batalkan (hanya saat DIAJUKAN atau DIJADWALKAN)
- Konfirmasi hasil timbangan (saat MENUNGGU_KONFIRMASI)
  ★ Ini yang memicu pemotongan komisi
- Ajukan sengketa (saat MENUNGGU_KONFIRMASI) + unggah bukti
- Beri ulasan (saat SELESAI dan belum diulas)

Konfirmasi wajib memakai modal CSS :target, BUKAN JavaScript.
```

### 3.3 — Daftar Harga Pengepul (Defry, 2410631170066)

```
Bangun modul pengaturan harga pengepul:

GET  /mitra/harga         daftar harga dikelompokkan per golongan
POST /mitra/harga         simpan semua harga sekaligus (satu form besar)

Tiap baris kategori menampilkan:
- Nama kategori + ikon
- Input harga per kg
- Input berat minimum
- Sakelar "sedang menerima" (checkbox)
- ★ Perbandingan terhadap indeks: pakai HargaPengepul::posisiTerhadapIndeks()
  Tampilkan lencana "12% di bawah indeks" dengan warna sesuai
- Rentang acuan resmi sebagai panduan

ATURAN: harga menyimpang TIDAK diblokir. Hanya diberi peringatan visual
bahwa warga akan melihat labelnya. Kalau menyimpang lebih dari
ambang_peringatan_harga, tampilkan konfirmasi sebelum menyimpan.

Kategori limbah_b3 hanya muncul bila profil pengepul punya izin_b3.

Buat App\Services\HargaPengepulService dan
App\Http\Requests\Pengepul\SimpanHargaRequest.
```

### 3.4 — Alur Penjemputan Pengepul (Defry)

```
Bangun modul permintaan untuk pengepul:

GET  /mitra/permintaan           daftar, saring per status
GET  /mitra/permintaan/{kode}    rincian
POST /mitra/permintaan/{kode}/terima    (middleware saldo.cukup)
POST /mitra/permintaan/{kode}/tolak     + alasan wajib
POST /mitra/permintaan/{kode}/berangkat
POST /mitra/permintaan/{kode}/timbang   ★ terpenting

GET  /mitra/terbuka              permintaan terbuka dalam radius
POST /mitra/terbuka/{kode}/klaim ★ siapa cepat dia dapat

Form timbang:
- Tiap item menampilkan estimasi warga sebagai pembanding
- Input berat aktual (wajib) dan harga final (terisi harga beku, bisa diubah)
- Bila harga final di bawah harga beku, WAJIB mengisi alasan
- Hitung ulang total, lalu ubah status ke MENUNGGU_KONFIRMASI

Klaim permintaan terbuka WAJIB memakai lockForUpdate() di dalam
DB::transaction agar dua pengepul tidak bisa mengklaim permintaan yang sama.

Tambahkan metode ke PermintaanJemputService: terima, tolak, berangkat,
timbang, klaimTerbuka.
```

### 3.5 — Dompet & Komisi (Defry)

```
Bangun modul dompet pengepul:

GET  /mitra/dompet            saldo + riwayat mutasi + paginasi
GET  /mitra/dompet/topup      form pengajuan isi saldo
POST /mitra/dompet/topup      simpan + unggah bukti transfer

Halaman dompet menampilkan:
- Saldo besar, dengan peringatan merah bila di bawah minimum
- Rekening tujuan dari pengaturan_platform (bank_nama, bank_rekening,
  bank_atas_nama)
- Tabel mutasi: tanggal, jenis, keterangan, jumlah (+/-), saldo sesudah
- Saring per jenis mutasi

Buat App\Services\WalletService:

  public function kredit(ProfilPengepul $p, float $jumlah, JenisMutasi $jenis,
                         ?Model $referensi, string $keterangan): MutasiSaldo
  public function debit(...): MutasiSaldo

  WAJIB: DB::transaction + lockForUpdate pada baris profil_pengepul.
  WAJIB: catat saldo_sebelum dan saldo_sesudah.
  WAJIB: perbarui cache kolom profil_pengepul.saldo.

Buat App\Services\CommissionService:
  public function tagih(PermintaanJemput $permintaan): MutasiSaldo
  Dipanggil HANYA saat warga mengonfirmasi transaksi selesai.
  Bekukan tarif_komisi dan jumlah_komisi ke baris permintaan.

Buat App\Services\TopupService: ajukan, setujui, tolak.
```

### 3.6 — Verifikasi Pengepul (Defry + Diego)

```
Sisi pengepul:
GET  /mitra/verifikasi         form unggah KTP + foto lapak + centang izin B3
POST /mitra/verifikasi         simpan, ubah status ke MENUNGGU
GET  /mitra/verifikasi/status  tampilkan status, alasan penolakan bila ada

Sisi admin:
GET  /admin/verifikasi              antrean, saring status
GET  /admin/verifikasi/{pengepul}   lihat berkas
POST /admin/verifikasi/{pengepul}/setujui
POST /admin/verifikasi/{pengepul}/tolak   + alasan wajib

Berkas disimpan di storage/app/public. Jalankan php artisan storage:link.
Validasi: gambar, maksimal 2MB, jpg/jpeg/png.

Buat App\Services\VerifikasiService dan catat semua keputusan ke log_audit.
```

### 3.7 — Modul Admin (Diego, 2410631170068)

```
Bangun seluruh modul admin. Semuanya memakai <x-layouts.panel>,
tabel dengan paginasi, dan penyaring berbentuk form GET.

a) /admin/pengguna — daftar semua peran, cari, saring, suspend/aktifkan,
   atur ulang kata sandi. Jangan biarkan admin men-suspend dirinya sendiri.

b) /admin/kategori — CRUD kategori sampah. Termasuk faktor_co2_per_kg,
   penanda limbah_b3 beserta teks peringatannya, dan rentang harga acuan.

c) /admin/harga — dua tab (memakai query string, bukan JavaScript):
   Tab 1: CRUD sumber_harga_pasar. Wajib mengisi sumber_nama dan
          sumber_tipe, boleh melampirkan dokumen bukti.
   Tab 2: pantau indeks_harga + tombol hitung ulang manual
          (memanggil IndeksHargaService::hitungSemua)

d) /admin/transaksi — pantau semua permintaan, saring status/tanggal/
   pengepul, ekspor CSV.

e) /admin/sengketa — antrean sengketa. Halaman rincian menampilkan
   perbandingan harga beku vs harga final secara jelas, beserta buktinya.
   Aksi: menangkan warga (batalkan + kembalikan komisi),
   menangkan pengepul (selesaikan transaksi), atau jatuhkan sanksi.

f) /admin/topup — antrean, lihat bukti transfer, setujui/tolak.
   Menyetujui memanggil WalletService::kredit.

g) /admin/pengaturan — form pengaturan_platform dikelompokkan per grup.

h) /admin/log — log audit, saring per pengguna dan aksi.

Semua aksi yang mengubah data wajib tercatat di log_audit.
Buat Observer untuk pencatatan otomatis.
```

### 3.8 — Penjadwalan & Perintah Konsol (Diego)

```
Buat dua perintah artisan:

php artisan rongsokku:hitung-indeks [--hari=0]
  Memanggil IndeksHargaService::hitungSemua.

php artisan rongsokku:segarkan-metrik
  Memanggil MetrikPengepulService::segarkan untuk semua pengepul.

Daftarkan di routes/console.php:
  Schedule::command('rongsokku:hitung-indeks')->dailyAt('00:05');
  Schedule::command('rongsokku:segarkan-metrik')->dailyAt('00:15');
```

### 3.9 — Pengujian (Diego + Andhika)

```
Buat pengujian dengan PHPUnit:

tests/Unit/
  HaversineTest          jarak dua titik yang sudah diketahui
  IndeksHargaTest        median ganjil, genap, dan larik kosong
  CommissionTest         perhitungan komisi + pembulatan
  MetrikPengepulTest     skor kepatuhan harga

tests/Feature/
  AuthTest               daftar, masuk, keluar, isolasi peran
  PermintaanJemputTest   alur lengkap diajukan sampai selesai
  WalletTest             saldo tidak pernah minus, buku besar konsisten
  HargaPengepulTest      harga beku tidak berubah setelah pengepul
                         mengubah daftar harganya

Wajib diuji:
- Pengepul dengan saldo kurang tidak bisa menerima permintaan
- Komisi tidak dipotong sebelum status SELESAI
- Dua pengepul tidak bisa mengklaim permintaan terbuka yang sama
- Warga tidak bisa melihat permintaan milik warga lain

Untuk laporan, buat juga:
docs/pengujian/black-box-equivalence-partitioning.md
docs/pengujian/white-box-cyclomatic-complexity.md
  Terapkan pada PermintaanJemputService::konfirmasiSelesai(),
  lengkap dengan flow graph dan perhitungan V(G).
```

---

## 4. Prompt perbaikan cepat

**Menambah halaman baru**
```
Tambahkan halaman [X] di [rute]. Pakai <x-layouts.panel> untuk panel atau
<x-layouts.publik> untuk halaman publik. Pakai ulang komponen yang ada di
resources/views/components/. Tanpa JavaScript. Teks UI Bahasa Indonesia.
Logika bisnis taruh di Service, bukan Controller.
```

**Menambah interaksi tanpa JavaScript**
```
Saya butuh [interaksi] tanpa JavaScript. Pakai salah satu teknik ini:
- dropdown/panel  : <input type="checkbox" class="peer hidden"> + peer-checked:
- modal           : pseudo-class CSS :target lewat anchor #id
- akordeon        : <details><summary>
- tab             : tautan dengan query string, ditangani Controller
- saring/cari     : form GET + $request->query()
- grafik          : <svg> dirender Blade
Kalau tidak ada yang cocok, ubah rancangan fiturnya. Jangan tambah JavaScript.
```

**Menelusuri galat**
```
Terjadi galat: [pesan]. Periksa storage/logs/laravel.log, temukan akar
masalahnya, lalu perbaiki. Jangan menambal gejalanya saja.
Ingat batasan di CLAUDE.md.
```

---

## 5. Perintah harian

```bash
# Nyalakan MySQL lewat XAMPP Control Panel, atau:
/c/xampp/mysql/bin/mysqld.exe --defaults-file=/c/xampp/mysql/bin/my.ini --standalone &

php artisan migrate:fresh --seed   # bangun ulang database + data demo
npm run build                      # build CSS
php artisan serve                  # http://127.0.0.1:8000
php artisan test                   # jalankan pengujian
./vendor/bin/pint                  # rapikan format kode

# Akun demo, kata sandi semuanya: password
# admin@rongsokku.test  |  andhika@warga.test  |  jaya@pengepul.test
```

---

## 6. Yang membedakan RongsokKu dari Rongsokin

Bahan untuk bab Tinjauan Pustaka. Rongsokin (ITS) sudah tidak beroperasi.

| Aspek | Rongsokin | RongsokKu |
|---|---|---|
| Penentuan harga | Dipatok platform | Pengepul menentukan sendiri, dibandingkan indeks |
| Pemilihan pengepul | Broadcast saja | Pilih langsung, plus opsi permintaan terbuka |
| Pembayaran ke warga | Poin → e-money | Tunai langsung di lokasi |
| Model pendapatan | Tidak disebutkan | Komisi dari saldo prabayar pengepul |
| Risiko regulasi | Poin yang dapat dicairkan berpotensi masuk ranah uang elektronik | Uang tidak pernah melewati platform |
| Sumber harga pasar | Ditetapkan sepihak | Indeks dari transaksi nyata + acuan bersumber jelas |

Tiga alasan yang patut diduga menyebabkan Rongsokin berhenti, dan cara
RongsokKu menghindarinya:

1. **Poin yang dapat dicairkan menjadi uang** menunggu kerja sama dengan OJK,
   perbankan, dan penyedia e-money yang tak kunjung terwujud.
   → RongsokKu tidak pernah memegang uang transaksi sama sekali.
2. **Harga tetap** tidak tahan terhadap fluktuasi harga komoditas.
   → RongsokKu membiarkan pengepul menentukan harga, dan hanya menampilkan
     perbandingannya terhadap indeks.
3. **Tidak ada model pendapatan** yang jelas.
   → RongsokKu memungut komisi dari saldo prabayar pengepul sejak awal.

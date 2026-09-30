# Rancangan Sistem RongsokKu

Dokumen arsitektur untuk tim pengembang. Aturan kerja harian ada di `CLAUDE.md`;
dokumen ini menjelaskan **apa** yang dibangun dan **kenapa** dirancang begitu.

Dasar rancangan: Business Model Canvas dan validasi pasar terhadap 10 warga dan
2 pengepul di Karawang (`docs/BMC_RongsokKu_*.pptx`).

---

## 1. Masalah dan jawaban

| Temuan validasi | Jawaban di sistem |
|---|---|
| 100% warga mengeluhkan harga tidak transparan | Pusat Harga Pasar publik + harga tiap pengepul terlihat sebelum memilih |
| Warga menunggu pemulung lewat | Cari pengepul terdekat + ajukan penjemputan |
| Pengepul mencatat di bon kertas | Semua transaksi, timbangan, dan komisi tercatat otomatis |
| Pengepul khawatir penipuan / isu kepercayaan | Verifikasi identitas, rating, dan metrik objektif |
| Sebagian pengepul hanya akrab dengan WhatsApp | Tombol WhatsApp langsung setelah permintaan diterima |

## 2. Peran

| Peran | Yang dilakukan |
|---|---|
| **Warga** | Menjual rongsok: pilih pengepul, ajukan jemput, konfirmasi timbangan, beri ulasan |
| **Pengepul** | Membeli rongsok: pasang harga, terima/klaim permintaan, timbang, bayar tunai, isi saldo |
| **Admin** | Verifikasi pengepul & top-up, tangani sengketa, kelola kategori/harga acuan/pengguna/pengaturan |

## 3. Keputusan desain utama

1. **Model bisnis komisi 5%**, dipotong dari **saldo prabayar** pengepul. Uang
   hasil penjualan dibayar tunai langsung oleh pengepul ke warga dan tidak pernah
   melewati platform, sehingga platform tidak menjadi penyelenggara pembayaran.
2. **Harga ditentukan pengepul sendiri, tidak diblokir.** Penyimpangan dari indeks
   hanya diberi label. Harga tetap (pola Rongsokin) rapuh terhadap fluktuasi harga
   komoditas.
3. **Harga dibekukan saat pengajuan** di `item_permintaan.harga_estimasi_per_satuan`.
   Inilah dasar metrik kepatuhan harga dan bukti dalam sengketa.
4. **Rating murni menilai layanan.** Perilaku harga diukur terpisah lewat
   *skor kepatuhan harga* yang dihitung dari data, bukan dari penilaian warga.
5. **Indeks Harga RongsokKu** dihitung dari transaksi nyata (median 30 hari,
   minimal 5 transaksi). Belum ada lembaga resmi yang menerbitkan harga rongsok
   secara berkala, jadi indeks dilengkapi harga acuan dari sumber lokal yang
   dicantumkan asal-usulnya.
6. **Nol JavaScript.** Semua interaksi diproses server; modal memakai CSS
   `:target`, akordeon memakai `<details>`, grafik memakai SVG, peta memakai
   `iframe` OpenStreetMap (gratis, tanpa API key).

## 4. Arsitektur berlapis

```
Route ─► Middleware ─► FormRequest ─► Controller (tipis) ─► Service ─► Model ─► MySQL
            │               │                                   │
   peran, verifikasi,   validasi +                  aturan bisnis, DB::transaction,
   saldo cukup          pesan Indonesia             lockForUpdate, AksiTidakValid
```

Controller hanya menerima input, memanggil service, lalu mengembalikan view atau
redirect. Pelanggaran aturan dilempar sebagai `AksiTidakValid`, dan handler
global mengubahnya menjadi pesan galat di halaman sebelumnya.

## 5. Model data

```
wilayah (provinsi > kabupaten > kecamatan > kelurahan, + lat/lng)
   │
users ──1:1── profil_pengepul ──1:N── harga_pengepul ──N:1── kategori_sampah (induk > turunan)
   │               │   │                                          │
   │               │   ├──1:N── mutasi_saldo   (buku besar)        ├── sumber_harga_pasar
   │               │   └──1:N── permintaan_topup                   └── indeks_harga (harian)
   │               │
   └──1:N── permintaan_jemput ──1:N── item_permintaan (estimasi vs final)
                   │
                   ├──1:1── ulasan
                   └──1:1── sengketa

lencana ──N:M── users        pengaturan_platform        log_audit
```

Catatan penting:

- `mutasi_saldo` adalah **sumber kebenaran** saldo; `profil_pengepul.saldo`
  hanya cache. Tiap baris mutasi menyimpan saldo sebelum dan sesudah.
- `permintaan_jemput.profil_pengepul_id` boleh `NULL` untuk **permintaan terbuka**.
- `tarif_komisi` dibekukan per transaksi agar riwayat tetap akurat walau tarif diubah.

## 6. Mesin status permintaan

```
DIAJUKAN ──terima──► DIJADWALKAN ──berangkat──► DIJEMPUT ──timbang──► MENUNGGU_KONFIRMASI
   │                     │                        │                      │         │
   ├─tolak─► DITOLAK     └─batal─► DIBATALKAN ◄───┘            konfirmasi│         │tolak hasil
   └─batal─► DIBATALKAN                                                  ▼         ▼
                                                                     SELESAI ◄─ SENGKETA ─► DIBATALKAN
                                                                  ★ komisi dipotong   (keputusan admin)
```

| Transisi | Pelaku | Penjaga |
|---|---|---|
| terima | pengepul | status DIAJUKAN, saldo ≥ minimum |
| klaim terbuka | pengepul | belum diklaim (baris dikunci), menerima semua kategori, izin B3 bila perlu |
| timbang | pengepul | status DIJEMPUT; harga di bawah kesepakatan wajib beralasan |
| konfirmasi | warga | status MENUNGGU_KONFIRMASI → selesaikan + potong komisi |
| sengketa | warga | status MENUNGGU_KONFIRMASI |
| putuskan | admin | tiga pilihan: harga awal / hasil timbang / batalkan, opsional sanksi |

## 7. Alur keuangan

```
Pengepul transfer ─► ajukan top-up + bukti ─► admin setujui ─► WalletService::kredit
Transaksi SELESAI ─► CommissionService (di PermintaanJemputService::selesaikan)
                    ─► WalletService::debit (komisi = total_final × tarif)
Saldo < minimum ─► pengepul tidak bisa terima/klaim permintaan baru
```

Saldo boleh minus bila satu komisi melebihi saldo tersisa; minus diperlakukan
sebagai utang dan otomatis menahan pengepul sampai saldo diisi.

## 8. Metrik objektif pengepul

| Metrik | Rumus |
|---|---|
| Kepatuhan harga | % transaksi selesai yang dibayar ≥ 99% harga beku di semua item |
| Tingkat penerimaan | % permintaan yang diterima dari semua yang direspons |
| Ketepatan waktu | % penjemputan pada atau sebelum tanggal jadwal |
| Pembatalan sepihak | jumlah pembatalan oleh pengepul |

Dihitung ulang setiap kali transaksi selesai, ditolak, dibatalkan, atau diulas,
dan setiap malam lewat `rongsokku:segarkan-metrik`.

## 9. Modul dan penanggung jawab

| Modul | Penanggung jawab | Halaman utama |
|---|---|---|
| Fondasi, sistem desain, publik | Andhika | beranda, pusat harga, cari pengepul, peringkat |
| Warga | Rizky | wizard ajukan (3 langkah), permintaan saya, dampak & lencana, profil |
| Pengepul | Defry | verifikasi, daftar harga, permintaan masuk, terbuka, dompet, lapak, performa |
| Admin | Diego | verifikasi, top-up, transaksi + CSV, sengketa, kategori, harga & indeks, pengguna, pengaturan, log |

## 10. Keamanan dan privasi

- Hak akses peran lewat middleware `peran:*`; kepemilikan data lewat
  `PermintaanJemputPolicy` dan pemeriksaan di service.
- KTP, bukti transfer, foto barang, dan bukti sengketa disimpan di disk `local`
  dan disajikan hanya lewat `BerkasController` setelah diperiksa hak aksesnya.
- Nomor WhatsApp kedua pihak baru tampil setelah permintaan diterima.
- Login dan pendaftaran dibatasi 10 percobaan per menit.
- Semua tindakan admin dan keuangan tercatat di `log_audit`.

## 11. Pengujian

- **Otomatis (PHPUnit)**: `php artisan test` — alur penjemputan lengkap, pembekuan
  harga, komisi, saldo kurang, klaim ganda permintaan terbuka, limbah B3, top-up,
  verifikasi, sengketa, rating terpisah dari harga, isolasi hak akses, nol JavaScript.
- **Black box**: tabel *equivalence partitioning* per form (pengajuan, timbang,
  top-up, verifikasi).
- **White box**: *cyclomatic complexity* pada `PermintaanJemputService::timbang()`
  dan `SengketaService::putuskan()` — metode dengan percabangan terbanyak.

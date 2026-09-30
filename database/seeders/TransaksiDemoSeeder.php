<?php

namespace Database\Seeders;

use App\Enums\JenisMutasi;
use App\Enums\PeranPengguna;
use App\Enums\StatusPermintaan;
use App\Enums\StatusTopup;
use App\Models\HargaPengepul;
use App\Models\ItemPermintaan;
use App\Models\MutasiSaldo;
use App\Models\PermintaanJemput;
use App\Models\PermintaanTopup;
use App\Models\ProfilPengepul;
use App\Models\Ulasan;
use App\Models\User;
use App\Support\Haversine;
use Illuminate\Database\Seeder;

class TransaksiDemoSeeder extends Seeder
{
    public function run(): void
    {
        $warga = User::peran(PeranPengguna::Warga)->with('wilayah')->get();
        $pengepul = ProfilPengepul::with(['user', 'harga'])->get();
        $tarifKomisi = (float) pengaturan('tarif_komisi', 5);

        $komentar = [
            'Pengepulnya datang tepat waktu, timbangan jelas. Harga sesuai yang tertera di aplikasi.',
            'Ramah dan cepat. Barang langsung diangkut, uang dibayar tunai di tempat.',
            'Prosesnya gampang banget. Tidak perlu repot antar barang sendiri.',
            'Timbangan ditunjukkan langsung ke saya, jadi tidak ada yang ditutupi.',
            'Harga sedikit di bawah perkiraan karena kardusnya lembab, tapi dijelaskan dengan baik.',
            'Sangat membantu, rumah jadi lega. Pasti pakai lagi bulan depan.',
            'Sudah sesuai. Tapi jam datangnya agak mundur dari jadwal.',
            'Bagus, pengepul sopan dan barang dihitung satu per satu.',
        ];

        $nomor = 1;
        $nomorTopup = 1;
        $saldoMinimum = (float) pengaturan('saldo_minimum', 10000);

        // 420 transaksi selesai selama 60 hari terakhir. Jumlah ini cukup
        // untuk membuat Indeks Harga memiliki data yang bermakna di semua
        // kategori. Urutkan menurut waktu agar mutasi saldo tercatat runtut.
        $jadwal = collect(range(0, 419))
            ->map(fn () => now()->subDays(rand(0, 59))->subHours(rand(0, 23)))
            ->sort()
            ->values();

        foreach ($jadwal as $selesaiPada) {
            $w = $warga->random();
            $p = $pengepul->random();
            $hargaTersedia = $p->harga->where('sedang_menerima', true);

            if ($hargaTersedia->isEmpty()) {
                continue;
            }

            $hariLalu = (int) $selesaiPada->diffInDays(now());

            $permintaan = PermintaanJemput::create([
                'kode' => 'RSK-'.str_pad((string) $nomor++, 6, '0', STR_PAD_LEFT),
                'warga_id' => $w->id,
                'profil_pengepul_id' => $p->id,
                'status' => StatusPermintaan::Selesai,
                'wilayah_id' => $w->wilayah_id,
                'alamat_jemput' => $w->alamat_detail,
                'latitude' => $w->latitude,
                'longitude' => $w->longitude,
                'jarak_km' => Haversine::jarak(
                    (float) $p->user->latitude, (float) $p->user->longitude,
                    (float) $w->latitude, (float) $w->longitude
                ),
                'jadwal_tanggal' => $selesaiPada->copy()->toDateString(),
                'jadwal_sesi' => ['pagi', 'siang', 'sore'][rand(0, 2)],
                'diterima_pada' => $selesaiPada->copy()->subDays(1),
                'dijemput_pada' => $selesaiPada->copy()->subHours(2),
                'ditimbang_pada' => $selesaiPada->copy()->subMinutes(30),
                'selesai_pada' => $selesaiPada,
                'created_at' => $selesaiPada->copy()->subDays(2),
                'updated_at' => $selesaiPada,
            ]);

            $estimasiTotal = 0;
            $finalTotal = 0;
            $estimasiBerat = 0;
            $finalBerat = 0;

            /** @var \Illuminate\Support\Collection<int, HargaPengepul> $dipilih */
            $dipilih = $hargaTersedia->random(min(rand(1, 3), $hargaTersedia->count()));
            $dipilih = $dipilih instanceof HargaPengepul ? collect([$dipilih]) : $dipilih;

            foreach ($dipilih as $h) {
                $beratEstimasi = round(rand(20, 250) / 10, 2);
                // Berat aktual hasil timbangan biasanya meleset dari taksiran warga.
                $beratFinal = round($beratEstimasi * (rand(75, 115) / 100), 2);

                // Gelombang harga lembut mengikuti waktu, meniru pergerakan
                // harga komoditas agar grafik tren indeks tidak datar.
                $drift = 1 + sin($hariLalu / 14) * 0.07;
                $hargaBeku = round((float) $h->harga_per_satuan * $drift / 25) * 25;

                // Sebagian kecil transaksi dibayar di bawah harga yang dipajang,
                // agar metrik Kepatuhan Harga punya variasi nyata.
                $hargaFinal = rand(1, 10) === 1
                    ? round($hargaBeku * (rand(80, 95) / 100) / 50) * 50
                    : $hargaBeku;

                $subEstimasi = round($beratEstimasi * $hargaBeku, 2);
                $subFinal = round($beratFinal * $hargaFinal, 2);

                ItemPermintaan::create([
                    'permintaan_jemput_id' => $permintaan->id,
                    'kategori_sampah_id' => $h->kategori_sampah_id,
                    'estimasi_berat' => $beratEstimasi,
                    'harga_estimasi_per_satuan' => $hargaBeku,
                    'subtotal_estimasi' => $subEstimasi,
                    'berat_final' => $beratFinal,
                    'harga_final_per_satuan' => $hargaFinal,
                    'subtotal_final' => $subFinal,
                ]);

                $estimasiTotal += $subEstimasi;
                $finalTotal += $subFinal;
                $estimasiBerat += $beratEstimasi;
                $finalBerat += $beratFinal;
            }

            $komisi = round($finalTotal * $tarifKomisi / 100, 2);

            $permintaan->update([
                'estimasi_total' => $estimasiTotal,
                'total_final' => $finalTotal,
                'estimasi_berat_kg' => $estimasiBerat,
                'berat_final_kg' => $finalBerat,
                'tarif_komisi' => $tarifKomisi,
                'jumlah_komisi' => $komisi,
            ]);

            // Pengepul mengisi saldo ketika hampir menyentuh batas minimum.
            // Tanpa ini saldo akan minus, dan itu melanggar aturan bahwa
            // pengepul bersaldo kurang tidak boleh menerima permintaan.
            if ((float) $p->saldo - $komisi < $saldoMinimum) {
                $this->isiSaldo($p, $selesaiPada->copy()->subHours(3), $nomorTopup++);
            }

            $saldoSebelum = (float) $p->saldo;
            $saldoSesudah = $saldoSebelum - $komisi;

            MutasiSaldo::create([
                'profil_pengepul_id' => $p->id,
                'jenis' => JenisMutasi::Komisi,
                'jumlah' => -$komisi,
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah,
                'referensi_type' => PermintaanJemput::class,
                'referensi_id' => $permintaan->id,
                'keterangan' => "Komisi {$tarifKomisi}% transaksi {$permintaan->kode}",
                'created_at' => $selesaiPada,
                'updated_at' => $selesaiPada,
            ]);

            $p->saldo = $saldoSesudah;
            $p->total_transaksi++;
            $p->total_berat_kg += $finalBerat;
            $p->save();

            // Tidak semua transaksi diulas, seperti di dunia nyata.
            if (rand(1, 10) <= 7) {
                Ulasan::create([
                    'permintaan_jemput_id' => $permintaan->id,
                    'warga_id' => $w->id,
                    'profil_pengepul_id' => $p->id,
                    'rating' => [5, 5, 5, 4, 4, 4, 3, 5, 4, 2][rand(0, 9)],
                    'komentar' => $komentar[array_rand($komentar)],
                    'created_at' => $selesaiPada->copy()->addHours(rand(1, 48)),
                    'updated_at' => $selesaiPada->copy()->addHours(rand(1, 48)),
                ]);
            }
        }

        $this->buatPermintaanBerjalan($warga, $pengepul, $nomor);
    }

    /**
     * Catat satu pengisian saldo yang sudah disetujui admin, lengkap dengan
     * baris permintaan_topup-nya, supaya riwayat dompet terlihat utuh.
     */
    private function isiSaldo(ProfilPengepul $p, \Illuminate\Support\Carbon $waktu, int $nomor): void
    {
        $bank = ['Bank BCA', 'Bank BRI', 'Bank Mandiri', 'Bank BNI'][rand(0, 3)];
        $jumlah = [100_000, 150_000, 200_000, 300_000, 500_000][rand(0, 4)];

        $topup = PermintaanTopup::create([
            'kode' => 'TOP-'.str_pad((string) $nomor, 6, '0', STR_PAD_LEFT),
            'profil_pengepul_id' => $p->id,
            'jumlah' => $jumlah,
            'bank_pengirim' => $bank,
            'nama_pengirim' => $p->user->name,
            'status' => StatusTopup::Disetujui,
            'diverifikasi_oleh' => User::where('peran', PeranPengguna::Admin)->value('id'),
            'diverifikasi_pada' => $waktu,
            'created_at' => $waktu->copy()->subHour(),
            'updated_at' => $waktu,
        ]);

        $saldoSebelum = (float) $p->saldo;
        $saldoSesudah = $saldoSebelum + $jumlah;

        MutasiSaldo::create([
            'profil_pengepul_id' => $p->id,
            'jenis' => JenisMutasi::Topup,
            'jumlah' => $jumlah,
            'saldo_sebelum' => $saldoSebelum,
            'saldo_sesudah' => $saldoSesudah,
            'referensi_type' => PermintaanTopup::class,
            'referensi_id' => $topup->id,
            'keterangan' => "Pengisian saldo {$topup->kode} via {$bank}",
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ]);

        $p->saldo = $saldoSesudah;
        $p->save();
    }

    /** Beberapa permintaan yang masih berproses agar dashboard tidak kosong. */
    private function buatPermintaanBerjalan($warga, $pengepul, int $nomor): void
    {
        $status = [
            StatusPermintaan::Diajukan,
            StatusPermintaan::Diajukan,
            StatusPermintaan::Dijadwalkan,
            StatusPermintaan::Dijemput,
            StatusPermintaan::MenungguKonfirmasi,
        ];

        foreach ($status as $s) {
            $w = $warga->random();
            $p = $pengepul->random();
            $hargaTersedia = $p->harga->where('sedang_menerima', true);

            if ($hargaTersedia->isEmpty()) {
                continue;
            }

            $dibuat = now()->subDays(rand(0, 3))->subHours(rand(1, 20));

            $permintaan = PermintaanJemput::create([
                'kode' => 'RSK-'.str_pad((string) $nomor++, 6, '0', STR_PAD_LEFT),
                'warga_id' => $w->id,
                'profil_pengepul_id' => $p->id,
                'status' => $s,
                'wilayah_id' => $w->wilayah_id,
                'alamat_jemput' => $w->alamat_detail,
                'latitude' => $w->latitude,
                'longitude' => $w->longitude,
                'jarak_km' => Haversine::jarak(
                    (float) $p->user->latitude, (float) $p->user->longitude,
                    (float) $w->latitude, (float) $w->longitude
                ),
                'jadwal_tanggal' => now()->addDays(rand(0, 3))->toDateString(),
                'jadwal_sesi' => ['pagi', 'siang', 'sore'][rand(0, 2)],
                'catatan_warga' => 'Rumah pagar hijau, tolong hubungi dulu sebelum datang.',
                'diterima_pada' => $s === StatusPermintaan::Diajukan ? null : $dibuat->copy()->addHours(2),
                'created_at' => $dibuat,
                'updated_at' => $dibuat,
            ]);

            $total = 0;
            $berat = 0;

            $dipilih = $hargaTersedia->random(min(2, $hargaTersedia->count()));
            $dipilih = $dipilih instanceof HargaPengepul ? collect([$dipilih]) : $dipilih;

            foreach ($dipilih as $h) {
                $beratEstimasi = round(rand(30, 200) / 10, 2);
                $sub = round($beratEstimasi * (float) $h->harga_per_satuan, 2);

                $item = [
                    'permintaan_jemput_id' => $permintaan->id,
                    'kategori_sampah_id' => $h->kategori_sampah_id,
                    'estimasi_berat' => $beratEstimasi,
                    'harga_estimasi_per_satuan' => $h->harga_per_satuan,
                    'subtotal_estimasi' => $sub,
                ];

                // Pada tahap menunggu konfirmasi, hasil timbangan sudah terisi.
                if ($s === StatusPermintaan::MenungguKonfirmasi) {
                    $beratFinal = round($beratEstimasi * (rand(80, 110) / 100), 2);
                    $item['berat_final'] = $beratFinal;
                    $item['harga_final_per_satuan'] = $h->harga_per_satuan;
                    $item['subtotal_final'] = round($beratFinal * (float) $h->harga_per_satuan, 2);
                }

                ItemPermintaan::create($item);
                $total += $sub;
                $berat += $beratEstimasi;
            }

            $ubah = ['estimasi_total' => $total, 'estimasi_berat_kg' => $berat];

            if ($s === StatusPermintaan::MenungguKonfirmasi) {
                $permintaan->load('item');
                $ubah['total_final'] = $permintaan->item->sum('subtotal_final');
                $ubah['berat_final_kg'] = $permintaan->item->sum('berat_final');
                $ubah['ditimbang_pada'] = now()->subMinutes(20);
            }

            $permintaan->update($ubah);
        }

        // Dua permintaan terbuka yang belum diklaim pengepul mana pun.
        for ($i = 0; $i < 2; $i++) {
            $w = $warga->random();
            $p = $pengepul->random();
            $h = $p->harga->where('sedang_menerima', true)->first();

            if (! $h) {
                continue;
            }

            $permintaan = PermintaanJemput::create([
                'kode' => 'RSK-'.str_pad((string) $nomor++, 6, '0', STR_PAD_LEFT),
                'warga_id' => $w->id,
                'profil_pengepul_id' => null,
                'permintaan_terbuka' => true,
                'status' => StatusPermintaan::Diajukan,
                'wilayah_id' => $w->wilayah_id,
                'alamat_jemput' => $w->alamat_detail,
                'latitude' => $w->latitude,
                'longitude' => $w->longitude,
                'jadwal_tanggal' => now()->addDays(rand(1, 4))->toDateString(),
                'jadwal_sesi' => 'pagi',
                'catatan_warga' => 'Terbuka untuk pengepul mana pun yang bisa menjemput.',
            ]);

            $beratEstimasi = round(rand(50, 180) / 10, 2);
            $sub = round($beratEstimasi * (float) $h->harga_per_satuan, 2);

            ItemPermintaan::create([
                'permintaan_jemput_id' => $permintaan->id,
                'kategori_sampah_id' => $h->kategori_sampah_id,
                'estimasi_berat' => $beratEstimasi,
                'harga_estimasi_per_satuan' => $h->harga_per_satuan,
                'subtotal_estimasi' => $sub,
            ]);

            $permintaan->update(['estimasi_total' => $sub, 'estimasi_berat_kg' => $beratEstimasi]);
        }
    }
}

<?php

namespace Tests\Concerns;

use App\Enums\PeranPengguna;
use App\Enums\StatusVerifikasi;
use App\Models\HargaPengepul;
use App\Models\KategoriSampah;
use App\Models\PengaturanPlatform;
use App\Models\ProfilPengepul;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Str;

/** Data minimum untuk menguji alur RongsokKu tanpa menjalankan seeder demo. */
trait MembuatDataRongsokKu
{
    protected Wilayah $kelurahan;

    protected KategoriSampah $kardus;

    protected KategoriSampah $baterai;

    protected function siapkanDasar(): void
    {
        foreach ([
            ['tarif_komisi', '5'], ['saldo_minimum', '10000'], ['topup_minimum', '25000'],
            ['ambang_peringatan_harga', '15'], ['bank_nama', 'Bank BCA'], ['bank_rekening', '123'], ['bank_atas_nama', 'RongsokKu'],
        ] as [$kunci, $nilai]) {
            PengaturanPlatform::create(['kunci' => $kunci, 'nilai' => $nilai, 'label' => $kunci]);
        }

        $kecamatan = Wilayah::create(['nama' => 'Karawang Barat', 'tingkat' => 'kecamatan', 'latitude' => -6.31, 'longitude' => 107.29]);
        $this->kelurahan = Wilayah::create([
            'induk_id' => $kecamatan->id, 'nama' => 'Nagasari', 'tingkat' => 'kelurahan',
            'latitude' => -6.3059, 'longitude' => 107.2951,
        ]);

        $kertas = KategoriSampah::create(['nama' => 'Kertas', 'slug' => 'kertas', 'faktor_co2_per_kg' => 0.9]);
        $this->kardus = KategoriSampah::create([
            'induk_id' => $kertas->id, 'nama' => 'Kardus', 'slug' => 'kardus',
            'harga_acuan_min' => 1800, 'harga_acuan_max' => 2500, 'faktor_co2_per_kg' => 0.9,
        ]);

        $elektronik = KategoriSampah::create(['nama' => 'Elektronik', 'slug' => 'elektronik']);
        $this->baterai = KategoriSampah::create([
            'induk_id' => $elektronik->id, 'nama' => 'Baterai Bekas', 'slug' => 'baterai-bekas',
            'limbah_b3' => true, 'peringatan_b3' => 'Berbahaya', 'harga_acuan_min' => 1000, 'harga_acuan_max' => 3000,
        ]);
    }

    protected function buatWarga(string $nama = 'Siti Warga'): User
    {
        return User::create([
            'name' => $nama,
            'email' => Str::slug($nama).'-'.Str::random(4).'@warga.test',
            'password' => 'password',
            'peran' => PeranPengguna::Warga,
            'telepon' => '081311112222',
            'wilayah_id' => $this->kelurahan->id,
            'alamat_detail' => 'Jl. Merdeka No. 1',
            'latitude' => -6.3060,
            'longitude' => 107.2950,
        ]);
    }

    protected function buatPengepul(string $nama = 'Pengepul Jaya', float $saldo = 100000, float $hargaKardus = 2000, bool $izinB3 = false): ProfilPengepul
    {
        $user = User::create([
            'name' => $nama.' Pemilik',
            'email' => Str::slug($nama).'-'.Str::random(4).'@pengepul.test',
            'password' => 'password',
            'peran' => PeranPengguna::Pengepul,
            'telepon' => '081211112222',
            'wilayah_id' => $this->kelurahan->id,
            'alamat_detail' => 'Jl. Raya Nagasari',
            'latitude' => -6.3100,
            'longitude' => 107.2900,
        ]);

        $profil = ProfilPengepul::create([
            'user_id' => $user->id,
            'nama_usaha' => $nama,
            'slug' => Str::slug($nama).'-'.Str::lower(Str::random(4)),
            'status_verifikasi' => StatusVerifikasi::Terverifikasi,
            'izin_b3' => $izinB3,
            'radius_layanan_km' => 10,
        ]);
        $profil->forceFill(['saldo' => $saldo])->save();

        HargaPengepul::create([
            'profil_pengepul_id' => $profil->id,
            'kategori_sampah_id' => $this->kardus->id,
            'harga_per_satuan' => $hargaKardus,
        ]);

        return $profil->fresh(['user', 'harga']);
    }

    protected function buatAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin-'.Str::random(4).'@rongsokku.test',
            'password' => 'password',
            'peran' => PeranPengguna::Admin,
        ]);
    }

    /** Warga mengajukan lewat wizard HTTP sungguhan, lalu kembalikan permintaannya. */
    protected function ajukanLewatWizard(User $warga, ?ProfilPengepul $pengepul, float $berat = 10): \App\Models\PermintaanJemput
    {
        $this->actingAs($warga)
            ->get($pengepul ? route('warga.ajukan', ['pengepul' => $pengepul->slug]) : route('warga.ajukan', ['terbuka' => 1]))
            ->assertRedirect(route('warga.ajukan.barang'));

        $this->post(route('warga.ajukan.barang.tambah'), [
            'kategori_sampah_id' => $this->kardus->id,
            'estimasi_berat' => $berat,
        ])->assertSessionHasNoErrors();

        $this->post(route('warga.ajukan.kirim'), [
            'wilayah_id' => $this->kelurahan->id,
            'alamat_jemput' => 'Jl. Merdeka No. 1',
            'jadwal_tanggal' => now()->addDay()->toDateString(),
            'jadwal_sesi' => 'pagi',
        ])->assertSessionHasNoErrors();

        return $warga->permintaan()->latest('id')->firstOrFail();
    }
}

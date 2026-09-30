<?php

namespace Tests\Feature;

use App\Enums\StatusPermintaan;
use App\Enums\StatusTopup;
use App\Enums\StatusVerifikasi;
use App\Models\ProfilPengepul;
use App\Models\Ulasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatDataRongsokKu;
use Tests\TestCase;

class AdminDanKeuanganTest extends TestCase
{
    use MembuatDataRongsokKu, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanDasar();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_topup_disetujui_admin_menambah_saldo_dan_tercatat_di_buku_besar(): void
    {
        $pengepul = $this->buatPengepul(saldo: 0);
        $admin = $this->buatAdmin();

        $this->actingAs($pengepul->user)->post(route('pengepul.dompet.topup.kirim'), [
            'jumlah' => 100000,
            'bank_pengirim' => 'Bank BCA',
            'nama_pengirim' => 'Pemilik',
            'bukti_transfer' => UploadedFile::fake()->create('bukti.jpg', 50, 'image/jpeg'),
        ])->assertSessionHas('sukses');

        $topup = $pengepul->topup()->sole();
        $this->assertSame(StatusTopup::Menunggu, $topup->status);
        $this->assertEquals(0, (float) $pengepul->fresh()->saldo);

        $this->actingAs($admin)->post(route('admin.topup.setujui', $topup))->assertSessionHas('sukses');

        $this->assertEquals(100000, (float) $pengepul->fresh()->saldo);
        $this->assertEquals(100000, (float) $pengepul->mutasi()->sole()->saldo_sesudah);

        // Menyetujui dua kali tidak boleh menggandakan saldo.
        $this->post(route('admin.topup.setujui', $topup))->assertSessionHas('galat');
        $this->assertEquals(100000, (float) $pengepul->fresh()->saldo);
    }

    public function test_topup_di_bawah_minimum_ditolak(): void
    {
        $pengepul = $this->buatPengepul();

        $this->actingAs($pengepul->user)->post(route('pengepul.dompet.topup.kirim'), [
            'jumlah' => 10000,
            'bank_pengirim' => 'Bank BCA',
            'nama_pengirim' => 'Pemilik',
            'bukti_transfer' => UploadedFile::fake()->create('bukti.jpg', 50, 'image/jpeg'),
        ])->assertSessionHas('galat');

        $this->assertSame(0, $pengepul->topup()->count());
    }

    public function test_verifikasi_pengepul_baru_oleh_admin(): void
    {
        $admin = $this->buatAdmin();
        $pengepul = $this->buatPengepul('Lapak Baru');
        $pengepul->forceFill(['status_verifikasi' => StatusVerifikasi::Draf])->save();

        // Belum terverifikasi: halaman operasional dialihkan ke status verifikasi.
        $this->actingAs($pengepul->user)->get(route('pengepul.harga.index'))->assertRedirect(route('pengepul.verifikasi.status'));

        $this->post(route('pengepul.verifikasi.ajukan'), [
            'nama_usaha' => 'Lapak Baru',
            'foto_ktp' => UploadedFile::fake()->create('ktp.jpg', 50, 'image/jpeg'),
            'foto_lapak' => UploadedFile::fake()->create('lapak.jpg', 50, 'image/jpeg'),
        ])->assertSessionHas('sukses');

        $pengepul->refresh();
        $this->assertSame(StatusVerifikasi::Menunggu, $pengepul->status_verifikasi);
        Storage::disk('local')->assertExists($pengepul->foto_ktp);   // KTP tidak di disk publik
        Storage::disk('public')->assertExists($pengepul->foto_lapak);

        $this->actingAs($admin)->post(route('admin.verifikasi.setujui', $pengepul))->assertSessionHas('sukses');
        $this->assertSame(StatusVerifikasi::Terverifikasi, $pengepul->fresh()->status_verifikasi);

        $this->actingAs($pengepul->user)->get(route('pengepul.harga.index'))->assertOk();
    }

    public function test_sengketa_diputuskan_dengan_harga_awal(): void
    {
        $warga = $this->buatWarga();
        $pengepul = $this->buatPengepul(saldo: 100000, hargaKardus: 2000);
        $admin = $this->buatAdmin();
        $p = $this->ajukanLewatWizard($warga, $pengepul, berat: 10);

        $this->actingAs($pengepul->user)->post(route('pengepul.permintaan.terima', $p));
        $this->post(route('pengepul.permintaan.berangkat', $p));
        $item = $p->item()->first();
        $this->post(route('pengepul.permintaan.timbang', $p), [
            'hasil' => [$item->id => ['berat_final' => 10, 'harga_final' => 1500]],
            'catatan_pengepul' => 'Barang kotor',
        ]);

        $this->actingAs($warga)->post(route('warga.permintaan.sengketa', $p), [
            'alasan' => 'Harga diturunkan sepihak di lokasi',
            'deskripsi' => 'Di aplikasi tertulis Rp 2.000 per kg tetapi dibayar Rp 1.500 per kg.',
        ])->assertSessionHas('sukses');

        $this->assertSame(StatusPermintaan::Sengketa, $p->fresh()->status);
        $sengketa = $p->fresh()->sengketa;

        $this->actingAs($admin)->post(route('admin.sengketa.putuskan', $sengketa), [
            'keputusan' => 'harga_awal',
            'resolusi' => 'Pengepul wajib membayar selisih sesuai harga yang dipajang.',
        ])->assertSessionHas('sukses');

        $p->refresh();
        $this->assertSame(StatusPermintaan::Selesai, $p->status);
        $this->assertEquals(20000, (float) $p->total_final);
        $this->assertEquals(1000, (float) $p->jumlah_komisi);
        $this->assertEquals(99000, (float) $pengepul->fresh()->saldo);
        $this->assertSame('selesai', $sengketa->fresh()->status);
    }

    public function test_rating_tidak_dipengaruhi_harga(): void
    {
        $warga = $this->buatWarga();
        $pengepul = $this->buatPengepul(hargaKardus: 2000);
        $p = $this->ajukanLewatWizard($warga, $pengepul);

        $this->actingAs($pengepul->user)->post(route('pengepul.permintaan.terima', $p));
        $this->post(route('pengepul.permintaan.berangkat', $p));
        $this->post(route('pengepul.permintaan.timbang', $p), [
            'hasil' => [$p->item()->first()->id => ['berat_final' => 10, 'harga_final' => 1000]],
            'catatan_pengepul' => 'Turun harga',
        ]);
        $this->actingAs($warga)->post(route('warga.permintaan.konfirmasi', $p));
        $this->post(route('warga.permintaan.ulasan', $p), ['rating' => 5, 'komentar' => 'Ramah'])->assertSessionHas('sukses');

        $pengepul->refresh();
        $this->assertEquals(5, (float) $pengepul->rating_rata);           // bintang murni dari ulasan
        $this->assertEquals(0, (float) $pengepul->skor_kepatuhan_harga);  // perilaku harga tercatat terpisah

        // Ulasan hanya boleh sekali.
        $this->post(route('warga.permintaan.ulasan', $p), ['rating' => 1])->assertSessionHas('galat');
        $this->assertSame(1, Ulasan::count());
    }

    public function test_isolasi_hak_akses(): void
    {
        $warga = $this->buatWarga('Warga Satu');
        $lain = $this->buatWarga('Warga Dua');
        $pengepul = $this->buatPengepul();
        $pengepulLain = $this->buatPengepul('Lapak Lain');
        $p = $this->ajukanLewatWizard($warga, $pengepul);

        $this->actingAs($lain)->get(route('warga.permintaan.show', $p))->assertForbidden();
        $this->actingAs($lain)->post(route('warga.permintaan.konfirmasi', $p))->assertSessionHas('galat');
        $this->actingAs($pengepulLain->user)->get(route('pengepul.permintaan.show', $p))->assertForbidden();
        $this->actingAs($pengepulLain->user)->post(route('pengepul.permintaan.terima', $p))->assertSessionHas('galat');
        $this->actingAs($warga)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_dapat_menonaktifkan_pengguna_dan_pengguna_tidak_bisa_masuk(): void
    {
        $admin = $this->buatAdmin();
        $warga = $this->buatWarga();

        $this->actingAs($admin)->post(route('admin.pengguna.nonaktifkan', $warga), ['alasan' => 'Penipuan'])->assertSessionHas('sukses');
        $this->assertFalse($warga->fresh()->aktif);

        $this->post(route('logout'));
        $this->post(route('login'), ['email' => $warga->email, 'password' => 'password'])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('log_audit', ['aksi' => 'pengguna.dinonaktifkan', 'subjek_id' => $warga->id]);
    }
}

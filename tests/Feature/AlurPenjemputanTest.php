<?php

namespace Tests\Feature;

use App\Enums\JenisMutasi;
use App\Enums\StatusPermintaan;
use App\Models\HargaPengepul;
use App\Models\Lencana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataRongsokKu;
use Tests\TestCase;

class AlurPenjemputanTest extends TestCase
{
    use MembuatDataRongsokKu, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanDasar();
    }

    public function test_alur_lengkap_dari_pengajuan_sampai_komisi_terpotong(): void
    {
        $warga = $this->buatWarga();
        $pengepul = $this->buatPengepul(saldo: 100000, hargaKardus: 2000);
        $pemula = Lencana::create(['nama' => 'Pemula', 'slug' => 'pemula', 'ikon' => '🌱', 'deskripsi' => '-', 'syarat_berat_kg' => 1]);
        Lencana::create(['nama' => 'Jauh', 'slug' => 'jauh', 'ikon' => '🏆', 'deskripsi' => '-', 'syarat_berat_kg' => 500]);

        $p = $this->ajukanLewatWizard($warga, $pengepul, berat: 10);

        $this->assertSame(StatusPermintaan::Diajukan, $p->status);
        $this->assertEquals(20000, (float) $p->estimasi_total);

        $this->actingAs($pengepul->user)->post(route('pengepul.permintaan.terima', $p))->assertSessionHas('sukses');
        $this->post(route('pengepul.permintaan.berangkat', $p))->assertSessionHas('sukses');

        $item = $p->item()->first();
        $this->post(route('pengepul.permintaan.timbang', $p), [
            'hasil' => [$item->id => ['berat_final' => 8.3, 'harga_final' => 2000]],
        ])->assertSessionHas('sukses');

        $this->assertSame(StatusPermintaan::MenungguKonfirmasi, $p->fresh()->status);
        // Belum selesai berarti belum ada komisi sama sekali.
        $this->assertEquals(100000, (float) $pengepul->fresh()->saldo);
        $this->assertSame(0, $pengepul->mutasi()->count());

        $this->actingAs($warga)->post(route('warga.permintaan.konfirmasi', $p))->assertSessionHas('sukses');

        $p->refresh();
        $this->assertSame(StatusPermintaan::Selesai, $p->status);
        $this->assertEquals(16600, (float) $p->total_final);
        $this->assertEquals(830, (float) $p->jumlah_komisi);   // 5% × 16.600
        $this->assertEquals(5, (float) $p->tarif_komisi);

        $pengepul->refresh();
        $this->assertEquals(99170, (float) $pengepul->saldo);

        $mutasi = $pengepul->mutasi()->sole();
        $this->assertSame(JenisMutasi::Komisi, $mutasi->jenis);
        $this->assertEquals(100000, (float) $mutasi->saldo_sebelum);
        $this->assertEquals(99170, (float) $mutasi->saldo_sesudah);

        // 8,3 kg melewati ambang lencana 1 kg, tapi belum ambang 500 kg.
        $this->assertSame([$pemula->id], $warga->lencana()->pluck('lencana.id')->all());
    }

    public function test_harga_dibekukan_saat_pengajuan(): void
    {
        $warga = $this->buatWarga();
        $pengepul = $this->buatPengepul(hargaKardus: 2000);

        $p = $this->ajukanLewatWizard($warga, $pengepul, berat: 5);

        // Pengepul menurunkan harga di daftarnya SETELAH warga mengajukan.
        HargaPengepul::where('profil_pengepul_id', $pengepul->id)->update(['harga_per_satuan' => 1200]);

        $this->assertEquals(2000, (float) $p->item()->first()->harga_estimasi_per_satuan);
        $this->assertEquals(10000, (float) $p->fresh()->estimasi_total);
    }

    public function test_menurunkan_harga_saat_timbang_wajib_disertai_alasan_dan_menurunkan_kepatuhan(): void
    {
        $warga = $this->buatWarga();
        $pengepul = $this->buatPengepul(hargaKardus: 2000);
        $p = $this->ajukanLewatWizard($warga, $pengepul);

        $this->actingAs($pengepul->user)->post(route('pengepul.permintaan.terima', $p));
        $this->post(route('pengepul.permintaan.berangkat', $p));
        $item = $p->item()->first();

        $this->post(route('pengepul.permintaan.timbang', $p), [
            'hasil' => [$item->id => ['berat_final' => 10, 'harga_final' => 1500]],
        ])->assertSessionHas('galat');
        $this->assertSame(StatusPermintaan::Dijemput, $p->fresh()->status);

        $this->post(route('pengepul.permintaan.timbang', $p), [
            'hasil' => [$item->id => ['berat_final' => 10, 'harga_final' => 1500]],
            'catatan_pengepul' => 'Kardus basah',
        ])->assertSessionHas('sukses');

        $this->actingAs($warga)->post(route('warga.permintaan.konfirmasi', $p));

        $this->assertEquals(0, (float) $pengepul->fresh()->skor_kepatuhan_harga);
    }

    public function test_pengepul_bersaldo_kurang_tidak_bisa_menerima(): void
    {
        $warga = $this->buatWarga();
        $pengepul = $this->buatPengepul(saldo: 5000);
        $p = $this->ajukanLewatWizard($warga, $pengepul);

        $this->actingAs($pengepul->user)
            ->post(route('pengepul.permintaan.terima', $p))
            ->assertRedirect(route('pengepul.dompet.index'));

        $this->assertSame(StatusPermintaan::Diajukan, $p->fresh()->status);
    }

    public function test_permintaan_terbuka_hanya_bisa_diklaim_satu_pengepul(): void
    {
        $warga = $this->buatWarga();
        $a = $this->buatPengepul('Lapak A', hargaKardus: 2100);
        $b = $this->buatPengepul('Lapak B', hargaKardus: 2300);

        $p = $this->ajukanLewatWizard($warga, null, berat: 10);
        $this->assertTrue($p->permintaan_terbuka);
        $this->assertNull($p->profil_pengepul_id);

        $this->actingAs($a->user)->post(route('pengepul.terbuka.klaim', $p))->assertSessionHas('sukses');
        $this->actingAs($b->user)->post(route('pengepul.terbuka.klaim', $p))->assertSessionHas('galat');

        $p->refresh();
        $this->assertSame($a->id, $p->profil_pengepul_id);
        $this->assertSame(StatusPermintaan::Dijadwalkan, $p->status);
        // Harga pengepul yang mengklaim yang dibekukan.
        $this->assertEquals(2100, (float) $p->item()->first()->harga_estimasi_per_satuan);
    }

    public function test_limbah_b3_ditolak_untuk_pengepul_tanpa_izin(): void
    {
        $warga = $this->buatWarga();
        $pengepul = $this->buatPengepul(izinB3: false);
        HargaPengepul::create(['profil_pengepul_id' => $pengepul->id, 'kategori_sampah_id' => $this->baterai->id, 'harga_per_satuan' => 2000]);

        $this->actingAs($warga)->get(route('warga.ajukan', ['pengepul' => $pengepul->slug]));
        $this->post(route('warga.ajukan.barang.tambah'), [
            'kategori_sampah_id' => $this->baterai->id,
            'estimasi_berat' => 2,
        ])->assertSessionHas('galat');
    }

    public function test_warga_membatalkan_tidak_memotong_komisi(): void
    {
        $warga = $this->buatWarga();
        $pengepul = $this->buatPengepul();
        $p = $this->ajukanLewatWizard($warga, $pengepul);

        $this->actingAs($warga)->post(route('warga.permintaan.batal', $p), ['alasan' => 'Sudah dijual'])->assertSessionHas('sukses');

        $this->assertSame(StatusPermintaan::Dibatalkan, $p->fresh()->status);
        $this->assertSame(0, $pengepul->mutasi()->count());
    }
}

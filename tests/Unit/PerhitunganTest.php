<?php

namespace Tests\Unit;

use App\Services\IndeksHargaService;
use App\Support\Format;
use App\Support\Haversine;
use PHPUnit\Framework\TestCase;

class PerhitunganTest extends TestCase
{
    public function test_haversine_menghitung_jarak_yang_diketahui(): void
    {
        // Monas ke Bundaran HI kira-kira 2,2 km.
        $jarak = Haversine::jarak(-6.175392, 106.827153, -6.194971, 106.823036);
        $this->assertEqualsWithDelta(2.2, $jarak, 0.1);

        $this->assertSame(0.0, Haversine::jarak(-6.3, 107.3, -6.3, 107.3));
    }

    public function test_median_tahan_terhadap_pencilan(): void
    {
        $service = new IndeksHargaService;

        $this->assertSame(2000.0, $service->median([1900, 2000, 2100]));
        $this->assertSame(2050.0, $service->median([2000, 2100, 1900, 2200]));
        // Satu harga ekstrem tidak menggeser median.
        $this->assertSame(2000.0, $service->median([1900, 2000, 2100, 2000, 90000]));
        $this->assertSame(0.0, $service->median([]));
    }

    public function test_format_rupiah_dan_berat(): void
    {
        $this->assertSame('Rp 16.600', Format::rupiah(16600));
        $this->assertSame('Rp 1,5 jt', Format::rupiah(1500000, true));
        $this->assertSame('8,3 kg', Format::berat(8.3));
        $this->assertSame('10 kg', Format::berat(10));
        $this->assertSame('850 m', Format::jarak(0.85));
        $this->assertSame('6281234567890', Format::nomorWa('081234567890'));
    }
}

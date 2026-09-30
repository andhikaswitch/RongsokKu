<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataRongsokKu;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use MembuatDataRongsokKu, RefreshDatabase;

    public function test_halaman_publik_dapat_dibuka_tanpa_login(): void
    {
        $this->siapkanDasar();
        $pengepul = $this->buatPengepul();

        foreach (['/', '/harga', '/harga/kardus', '/peringkat', '/cara-kerja', '/tentang', '/faq', '/masuk', '/daftar', '/pengepul/'.$pengepul->slug] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_halaman_tidak_memuat_javascript(): void
    {
        $this->siapkanDasar();

        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('<script', $html);
        $this->assertDoesNotMatchRegularExpression('/\son(click|change|submit|load)=/i', $html);
    }
}

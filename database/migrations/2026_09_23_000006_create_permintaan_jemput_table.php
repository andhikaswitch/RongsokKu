<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_jemput', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->foreignId('warga_id')->constrained('users')->cascadeOnDelete();

            // Null berarti Permintaan Terbuka, terlihat oleh semua pengepul
            // dalam radius sampai ada yang mengklaim lebih dulu.
            $table->foreignId('profil_pengepul_id')->nullable()->constrained('profil_pengepul')->nullOnDelete();
            $table->boolean('permintaan_terbuka')->default(false);

            $table->string('status', 30)->default('diajukan');

            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah')->nullOnDelete();
            $table->string('alamat_jemput');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('jarak_km', 8, 2)->nullable();

            $table->date('jadwal_tanggal')->nullable();
            $table->string('jadwal_sesi', 20)->nullable();

            $table->decimal('estimasi_total', 12, 2)->default(0);
            $table->decimal('total_final', 12, 2)->nullable();
            $table->decimal('estimasi_berat_kg', 8, 2)->default(0);
            $table->decimal('berat_final_kg', 8, 2)->nullable();

            // Tarif dibekukan saat transaksi selesai agar riwayat tetap akurat
            // walaupun admin mengubah tarif komisi di kemudian hari.
            $table->decimal('tarif_komisi', 5, 2)->nullable();
            $table->decimal('jumlah_komisi', 12, 2)->nullable();

            $table->text('catatan_warga')->nullable();
            $table->text('catatan_pengepul')->nullable();
            $table->string('alasan_penolakan')->nullable();
            $table->string('dibatalkan_oleh', 20)->nullable();
            $table->string('alasan_pembatalan')->nullable();

            $table->timestamp('diterima_pada')->nullable();
            $table->timestamp('dijemput_pada')->nullable();
            $table->timestamp('ditimbang_pada')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamp('dibatalkan_pada')->nullable();
            $table->timestamps();

            $table->index(['warga_id', 'status']);
            $table->index(['profil_pengepul_id', 'status']);
            $table->index(['permintaan_terbuka', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_jemput');
    }
};

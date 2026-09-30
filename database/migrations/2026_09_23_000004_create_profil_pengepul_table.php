<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_pengepul', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nama_usaha');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('foto_lapak')->nullable();
            $table->string('foto_ktp')->nullable();

            $table->string('status_verifikasi', 20)->default('draf');
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('alasan_penolakan')->nullable();

            $table->boolean('izin_b3')->default(false);
            $table->time('jam_buka')->default('07:00:00');
            $table->time('jam_tutup')->default('17:00:00');
            $table->unsignedSmallInteger('radius_layanan_km')->default(10);
            $table->boolean('sedang_menerima')->default(true);

            // Cache saldo. Sumber kebenaran ada di tabel mutasi_saldo.
            $table->decimal('saldo', 12, 2)->default(0);

            $table->decimal('rating_rata', 3, 2)->default(0);
            $table->unsignedInteger('jumlah_ulasan')->default(0);
            $table->unsignedInteger('total_transaksi')->default(0);
            $table->decimal('total_berat_kg', 12, 2)->default(0);

            // Metrik objektif, dihitung ulang dari data transaksi.
            $table->decimal('skor_kepatuhan_harga', 5, 2)->default(100);
            $table->decimal('tingkat_penerimaan', 5, 2)->default(100);
            $table->decimal('ketepatan_waktu', 5, 2)->default(100);
            $table->unsignedInteger('pembatalan_sepihak')->default(0);

            $table->timestamps();

            $table->index(['status_verifikasi', 'sedang_menerima']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_pengepul');
    }
};

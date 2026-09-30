<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Harga acuan resmi yang dicatat admin, lengkap dengan jejak sumbernya
        // sehingga angka yang ditampilkan ke publik bisa dipertanggungjawabkan.
        Schema::create('sumber_harga_pasar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_sampah_id')->constrained('kategori_sampah')->cascadeOnDelete();
            $table->decimal('harga_min', 12, 2);
            $table->decimal('harga_max', 12, 2);
            $table->string('sumber_nama');
            $table->string('sumber_tipe', 30);
            $table->string('sumber_url')->nullable();
            $table->string('dokumen_bukti')->nullable();
            $table->date('berlaku_mulai');
            $table->date('berlaku_sampai')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['kategori_sampah_id', 'berlaku_mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sumber_harga_pasar');
    }
};

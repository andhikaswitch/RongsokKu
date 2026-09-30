<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_permintaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_jemput_id')->constrained('permintaan_jemput')->cascadeOnDelete();
            $table->foreignId('kategori_sampah_id')->constrained('kategori_sampah')->restrictOnDelete();

            // Harga dibekukan saat permintaan dibuat. Perubahan daftar harga
            // pengepul setelah ini tidak boleh mempengaruhi permintaan berjalan.
            $table->decimal('estimasi_berat', 8, 2);
            $table->decimal('harga_estimasi_per_satuan', 12, 2);
            $table->decimal('subtotal_estimasi', 12, 2);

            // Diisi pengepul saat menimbang di lokasi.
            $table->decimal('berat_final', 8, 2)->nullable();
            $table->decimal('harga_final_per_satuan', 12, 2)->nullable();
            $table->decimal('subtotal_final', 12, 2)->nullable();

            $table->string('foto_barang')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->index('permintaan_jemput_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_permintaan');
    }
};

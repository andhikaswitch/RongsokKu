<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Indeks Harga RongsokKu, dihitung dari transaksi yang benar-benar
        // selesai. Memakai median agar satu transaksi menyimpang tidak
        // menggeser angka yang dilihat publik.
        Schema::create('indeks_harga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_sampah_id')->constrained('kategori_sampah')->cascadeOnDelete();
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah')->nullOnDelete();
            $table->date('tanggal');
            $table->decimal('harga_median', 12, 2);
            $table->decimal('harga_min', 12, 2);
            $table->decimal('harga_max', 12, 2);
            $table->decimal('harga_rata', 12, 2);
            $table->unsignedInteger('jumlah_transaksi');
            $table->decimal('total_berat_kg', 12, 2);
            $table->decimal('perubahan_persen', 6, 2)->nullable();
            $table->timestamps();

            $table->unique(['kategori_sampah_id', 'wilayah_id', 'tanggal'], 'uniq_indeks_harian');
            $table->index(['kategori_sampah_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indeks_harga');
    }
};

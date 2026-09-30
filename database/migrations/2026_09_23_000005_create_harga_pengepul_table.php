<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harga_pengepul', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_pengepul_id')->constrained('profil_pengepul')->cascadeOnDelete();
            $table->foreignId('kategori_sampah_id')->constrained('kategori_sampah')->cascadeOnDelete();
            $table->decimal('harga_per_satuan', 12, 2);
            $table->decimal('min_berat', 8, 2)->default(0);
            $table->boolean('sedang_menerima')->default(true);
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->unique(['profil_pengepul_id', 'kategori_sampah_id'], 'uniq_harga_pengepul');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_pengepul');
    }
};

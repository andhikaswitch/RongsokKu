<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_sampah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('induk_id')->nullable()->constrained('kategori_sampah')->cascadeOnDelete();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->string('ikon', 16)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('satuan', 10)->default('kg');
            $table->decimal('harga_acuan_min', 12, 2)->nullable();
            $table->decimal('harga_acuan_max', 12, 2)->nullable();
            // Kilogram CO2 yang dicegah per kg material yang didaur ulang.
            $table->decimal('faktor_co2_per_kg', 8, 3)->default(0);
            // Limbah Bahan Berbahaya dan Beracun, hanya untuk pengepul berizin.
            $table->boolean('limbah_b3')->default(false);
            $table->text('peringatan_b3')->nullable();
            $table->boolean('aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['induk_id', 'aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_sampah');
    }
};

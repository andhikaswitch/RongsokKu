<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_jemput_id')->unique()->constrained('permintaan_jemput')->cascadeOnDelete();
            $table->foreignId('warga_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('profil_pengepul_id')->constrained('profil_pengepul')->cascadeOnDelete();
            // Rating murni menilai mutu layanan, tidak pernah dipengaruhi harga.
            $table->unsignedTinyInteger('rating');
            $table->text('komentar')->nullable();
            $table->text('balasan')->nullable();
            $table->timestamp('dibalas_pada')->nullable();
            $table->timestamps();

            $table->index(['profil_pengepul_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
    }
};

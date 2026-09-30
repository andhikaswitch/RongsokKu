<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sengketa', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->foreignId('permintaan_jemput_id')->constrained('permintaan_jemput')->cascadeOnDelete();
            $table->foreignId('dilaporkan_oleh')->constrained('users')->cascadeOnDelete();
            $table->string('alasan');
            $table->text('deskripsi');
            $table->string('bukti')->nullable();
            $table->string('status', 20)->default('menunggu');
            $table->text('resolusi')->nullable();
            $table->foreignId('ditangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditangani_pada')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sengketa');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_topup', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->foreignId('profil_pengepul_id')->constrained('profil_pengepul')->cascadeOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->string('bank_pengirim');
            $table->string('nama_pengirim');
            $table->string('bukti_transfer')->nullable();
            $table->string('status', 20)->default('menunggu');
            $table->string('catatan_admin')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();

            $table->index(['profil_pengepul_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_topup');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buku besar saldo pengepul. Ini sumber kebenaran, bukan kolom
        // profil_pengepul.saldo yang hanya berperan sebagai cache.
        Schema::create('mutasi_saldo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_pengepul_id')->constrained('profil_pengepul')->cascadeOnDelete();
            $table->string('jenis', 20);
            $table->decimal('jumlah', 12, 2);
            $table->decimal('saldo_sebelum', 12, 2);
            $table->decimal('saldo_sesudah', 12, 2);
            $table->nullableMorphs('referensi');
            $table->string('keterangan');
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['profil_pengepul_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_saldo');
    }
};

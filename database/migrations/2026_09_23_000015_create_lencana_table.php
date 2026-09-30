<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lencana', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->string('ikon', 16);
            $table->string('deskripsi');
            // Ambang berat kumulatif (kg) yang harus dicapai warga.
            $table->decimal('syarat_berat_kg', 10, 2);
            $table->string('warna', 40)->default('emerald');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('lencana_pengguna', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lencana_id')->constrained('lencana')->cascadeOnDelete();
            $table->timestamp('diraih_pada');
            $table->timestamps();

            $table->unique(['user_id', 'lencana_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lencana_pengguna');
        Schema::dropIfExists('lencana');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('induk_id')->nullable()->constrained('wilayah')->nullOnDelete();
            $table->string('nama');
            $table->enum('tingkat', ['provinsi', 'kabupaten', 'kecamatan', 'kelurahan']);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index(['tingkat', 'induk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};

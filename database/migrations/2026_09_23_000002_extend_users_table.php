<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('peran', 20)->default('warga')->after('email');
            $table->string('telepon', 20)->nullable()->after('peran');
            $table->string('foto_profil')->nullable()->after('telepon');
            $table->foreignId('wilayah_id')->nullable()->after('foto_profil')->constrained('wilayah')->nullOnDelete();
            $table->string('alamat_detail')->nullable()->after('wilayah_id');
            $table->decimal('latitude', 10, 7)->nullable()->after('alamat_detail');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->boolean('aktif')->default(true)->after('longitude');
            $table->timestamp('disuspend_pada')->nullable()->after('aktif');
            $table->string('alasan_suspend')->nullable()->after('disuspend_pada');

            $table->index('peran');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wilayah_id');
            $table->dropColumn([
                'peran', 'telepon', 'foto_profil', 'alamat_detail',
                'latitude', 'longitude', 'aktif', 'disuspend_pada', 'alasan_suspend',
            ]);
        });
    }
};

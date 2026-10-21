<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            if (!Schema::hasColumn('kegiatans', 'semester')) {
                $table->tinyInteger('semester')->default(1)->after('jenis')->index();
            }
            if (!Schema::hasColumn('kegiatans', 'tahun_ajaran')) {
                $table->string('tahun_ajaran', 20)->default('2026/2027')->after('semester')->index();
            }
        });

        // Backfill data yang sudah ada berdasarkan tanggal
        DB::statement("UPDATE kegiatans SET semester = CASE WHEN MONTH(tanggal) >= 7 AND MONTH(tanggal) <= 12 THEN 1 ELSE 2 END WHERE semester IS NULL OR semester = 1");
    }

    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            if (Schema::hasColumn('kegiatans', 'semester')) {
                $table->dropColumn('semester');
            }
            if (Schema::hasColumn('kegiatans', 'tahun_ajaran')) {
                $table->dropColumn('tahun_ajaran');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah dukungan tipe soal (PG, Menjodohkan, Essay) pada ujian_soals
        Schema::table('ujian_soals', function (Blueprint $table) {
            $table->string('tipe_soal', 20)->default('pg')->after('nomor_urut');
            $table->json('data_tambahan')->nullable()->after('kunci_jawaban');
            
            // Buat opsi & kunci jawaban nullable untuk tipe soal non-PG
            $table->text('opsi_a')->nullable()->change();
            $table->text('opsi_b')->nullable()->change();
            $table->text('opsi_c')->nullable()->change();
            $table->text('opsi_d')->nullable()->change();
            $table->text('kunci_jawaban')->nullable()->change();
        });

        // 2. Tambah kolom jawaban teks panjang & koreksi guru pada ujian_jawabans
        Schema::table('ujian_jawabans', function (Blueprint $table) {
            $table->longText('jawaban')->nullable()->change();
            $table->float('skor_diperoleh')->nullable()->after('is_benar');
            $table->text('catatan_koreksi')->nullable()->after('skor_diperoleh');
        });

        // 3. Tambah status koreksi pada ujian_siswas
        Schema::table('ujian_siswas', function (Blueprint $table) {
            $table->string('status_koreksi', 30)->default('selesai')->after('status'); // 'selesai', 'menunggu_koreksi'
        });
    }

    public function down(): void
    {
        Schema::table('ujian_siswas', function (Blueprint $table) {
            $table->dropColumn('status_koreksi');
        });

        Schema::table('ujian_jawabans', function (Blueprint $table) {
            $table->dropColumn(['skor_diperoleh', 'catatan_koreksi']);
        });

        Schema::table('ujian_soals', function (Blueprint $table) {
            $table->dropColumn(['tipe_soal', 'data_tambahan']);
        });
    }
};


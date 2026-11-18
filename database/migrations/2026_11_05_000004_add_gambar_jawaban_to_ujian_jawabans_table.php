<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujian_jawabans', function (Blueprint $table) {
            $table->string('gambar_jawaban')->nullable()->after('jawaban');
        });
    }

    public function down(): void
    {
        Schema::table('ujian_jawabans', function (Blueprint $table) {
            $table->dropColumn('gambar_jawaban');
        });
    }
};


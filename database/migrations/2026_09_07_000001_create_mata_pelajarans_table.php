<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_pelajarans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel')->unique();
            $table->string('nama_mapel');
            $table->string('tingkat', 20)->default('Semua');
            $table->integer('kkm')->default(75);
            $table->integer('bobot_tugas')->default(20);
            $table->integer('bobot_uh')->default(30);
            $table->integer('bobot_uts')->default(25);
            $table->integer('bobot_uas')->default(25);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_pelajarans');
    }
};

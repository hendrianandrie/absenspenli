<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatans')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->float('nilai')->default(0);
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->unique(['kegiatan_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};

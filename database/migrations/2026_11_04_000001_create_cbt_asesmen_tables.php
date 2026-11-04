<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Paket Ujian
        Schema::create('ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->onDelete('cascade');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('jenis', 20)->default('UH'); // UH, UTS, UAS, Sumatif
            $table->string('kelas', 50); // e.g. 'VII A', 'Semua', etc.
            $table->tinyInteger('semester')->default(1);
            $table->string('tahun_ajaran', 20)->nullable();
            $table->integer('durasi_menit')->default(60);
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('token', 10)->nullable();
            $table->boolean('acak_soal')->default(true);
            $table->boolean('acak_opsi')->default(false);
            $table->boolean('tampilkan_nilai')->default(true);
            $table->string('status', 20)->default('draft'); // draft, aktif, selesai
            $table->foreignId('kegiatan_id')->nullable()->constrained('kegiatans')->onDelete('set null');
            $table->timestamps();
        });

        // 2. Tabel Butir Soal Ujian (Pilihan Ganda A, B, C, D)
        Schema::create('ujian_soals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->integer('nomor_urut')->default(1);
            $table->longText('pertanyaan');
            $table->string('gambar')->nullable();
            $table->text('opsi_a');
            $table->text('opsi_b');
            $table->text('opsi_c');
            $table->text('opsi_d');
            $table->string('kunci_jawaban', 2); // 'A', 'B', 'C', 'D'
            $table->float('bobot')->default(1.0);
            $table->timestamps();
        });

        // 3. Tabel Sesi Ujian Siswa
        Schema::create('ujian_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->integer('sisa_waktu_detik')->nullable();
            $table->string('status', 20)->default('mengerjakan'); // 'mengerjakan', 'selesai', 'terkunci'
            $table->integer('jumlah_benar')->default(0);
            $table->integer('jumlah_salah')->default(0);
            $table->float('nilai_akhir')->default(0);
            $table->json('urutan_soal')->nullable();
            $table->timestamps();

            $table->unique(['ujian_id', 'siswa_id']);
        });

        // 4. Tabel Jawaban Siswa per Butir Soal
        Schema::create('ujian_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_siswa_id')->constrained('ujian_siswas')->onDelete('cascade');
            $table->foreignId('soal_id')->constrained('ujian_soals')->onDelete('cascade');
            $table->string('jawaban', 2)->nullable(); // 'A', 'B', 'C', 'D'
            $table->boolean('ragu_ragu')->default(false);
            $table->boolean('is_benar')->nullable();
            $table->timestamps();

            $table->unique(['ujian_siswa_id', 'soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_jawabans');
        Schema::dropIfExists('ujian_siswas');
        Schema::dropIfExists('ujian_soals');
        Schema::dropIfExists('ujians');
    }
};

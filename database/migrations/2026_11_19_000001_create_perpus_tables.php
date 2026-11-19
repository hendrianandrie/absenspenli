<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Kategori Buku
        Schema::create('perpus_kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->string('kode_kategori', 20)->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 2. Data Master Buku
        Schema::create('perpus_bukus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->nullable()->constrained('perpus_kategoris')->nullOnDelete();
            $table->string('kode_buku')->unique();
            $table->string('judul');
            $table->string('pengarang')->nullable();
            $table->string('penerbit')->nullable();
            $table->string('tahun_terbit', 10)->nullable();
            $table->string('isbn', 30)->nullable();
            $table->string('nomor_rak', 50)->nullable();
            $table->integer('stok_total')->default(1);
            $table->integer('stok_tersedia')->default(1);
            $table->text('deskripsi')->nullable();
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });

        // 3. Transaksi Peminjaman & Pengembalian
        Schema::create('perpus_peminjamans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 40)->unique();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('buku_id')->constrained('perpus_bukus')->cascadeOnDelete();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_jatuh_tempo');
            $table->date('tanggal_kembali')->nullable();
            $table->string('status', 20)->default('dipinjam'); // dipinjam, kembali, terlambat, hilang
            $table->decimal('denda', 10, 2)->default(0);
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
            $table->index('tanggal_pinjam');
        });

        // 4. Ulasan / Resume Buku Siswa
        Schema::create('perpus_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('perpus_peminjamans')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('buku_id')->constrained('perpus_bukus')->cascadeOnDelete();
            $table->string('judul_buku');
            $table->tinyInteger('rating')->default(5); // 1 - 5 bintang
            $table->text('ringkasan');
            $table->text('pesan_moral')->nullable();
            $table->string('status', 20)->default('menunggu'); // menunggu, disetujui, ditolak
            $table->integer('poin_diberikan')->default(0);
            $table->text('catatan_petugas')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
        });

        // 5. Riwayat Poin Literasi Siswa (Untuk Rekap Bulanan & Leaderboard)
        Schema::create('perpus_poin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->string('jenis_aktivitas', 40); // pinjam_tepat_waktu, resume_disetujui, terlambat, penyesuaian
            $table->integer('poin');
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->string('keterangan')->nullable();
            $table->tinyInteger('bulan'); // 1 - 12
            $table->smallInteger('tahun'); // e.g. 2026
            $table->timestamps();

            $table->index(['siswa_id', 'bulan', 'tahun']);
            $table->index(['bulan', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perpus_poin_logs');
        Schema::dropIfExists('perpus_resumes');
        Schema::dropIfExists('perpus_peminjamans');
        Schema::dropIfExists('perpus_bukus');
        Schema::dropIfExists('perpus_kategoris');
    }
};

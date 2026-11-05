<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ujian extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mata_pelajaran_id',
        'judul',
        'deskripsi',
        'jenis',
        'kelas',
        'semester',
        'tahun_ajaran',
        'durasi_menit',
        'waktu_mulai',
        'waktu_selesai',
        'token',
        'acak_soal',
        'acak_opsi',
        'tampilkan_nilai',
        'status',
        'kegiatan_id',
    ];

    protected $casts = [
        'durasi_menit' => 'integer',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'acak_soal' => 'boolean',
        'acak_opsi' => 'boolean',
        'tampilkan_nilai' => 'boolean',
        'semester' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function soals()
    {
        return $this->hasMany(UjianSoal::class, 'ujian_id')->orderBy('nomor_urut');
    }

    public function ujianSiswas()
    {
        return $this->hasMany(UjianSiswa::class, 'ujian_id');
    }

    public function generateToken(): string
    {
        $newToken = strtoupper(Str::random(5));
        $this->update(['token' => $newToken]);
        return $newToken;
    }

    public function isAktif(): bool
    {
        if ($this->status !== 'aktif') {
            return false;
        }

        $now = now();
        if ($this->waktu_mulai && $now->lt($this->waktu_mulai)) {
            return false;
        }
        if ($this->waktu_selesai && $now->gt($this->waktu_selesai)) {
            return false;
        }

        return true;
    }

    /**
     * Mendapatkan daftar kelas target sebagai array
     */
    public function getDaftarKelasAttribute(): array
    {
        if (empty($this->kelas) || $this->kelas === 'Semua') {
            return ['Semua'];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->kelas))));
    }

    /**
     * Cek apakah kelas siswa cocok dengan target ujian ini
     */
    public function matchesKelas(?string $kelas): bool
    {
        if (!$kelas) {
            return false;
        }

        if (empty($this->kelas) || $this->kelas === 'Semua') {
            return true;
        }

        return in_array($kelas, $this->daftar_kelas, true);
    }
}


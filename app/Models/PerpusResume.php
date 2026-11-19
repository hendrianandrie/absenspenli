<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerpusResume extends Model
{
    use HasFactory;

    protected $table = 'perpus_resumes';

    protected $fillable = [
        'peminjaman_id',
        'siswa_id',
        'buku_id',
        'judul_buku',
        'rating',
        'ringkasan',
        'pesan_moral',
        'status',
        'poin_diberikan',
        'catatan_petugas',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
        'rating' => 'integer',
        'poin_diberikan' => 'integer',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(PerpusPeminjaman::class, 'peminjaman_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function buku(): BelongsTo
    {
        return $this->belongsTo(PerpusBuku::class, 'buku_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}

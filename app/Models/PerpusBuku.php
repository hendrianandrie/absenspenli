<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerpusBuku extends Model
{
    use HasFactory;

    protected $table = 'perpus_bukus';

    protected $fillable = [
        'kategori_id',
        'kode_buku',
        'judul',
        'pengarang',
        'penerbit',
        'tahun_terbit',
        'isbn',
        'nomor_rak',
        'stok_total',
        'stok_tersedia',
        'deskripsi',
        'cover_image',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(PerpusKategori::class, 'kategori_id');
    }

    public function peminjamans(): HasMany
    {
        return $this->hasMany(PerpusPeminjaman::class, 'buku_id');
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(PerpusResume::class, 'buku_id');
    }

    public function getCoverUrlAttribute(): string
    {
        if ($this->cover_image && file_exists(public_path('storage/' . $this->cover_image))) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('images/default_book_cover.png');
    }
}

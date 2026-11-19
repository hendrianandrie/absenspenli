<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerpusKategori extends Model
{
    use HasFactory;

    protected $table = 'perpus_kategoris';

    protected $fillable = [
        'nama_kategori',
        'kode_kategori',
        'deskripsi',
    ];

    public function bukus(): HasMany
    {
        return $this->hasMany(PerpusBuku::class, 'kategori_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajarans';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'kkm',
    ];

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class);
    }
}

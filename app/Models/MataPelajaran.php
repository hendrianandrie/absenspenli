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
        'bobot_tugas',
        'bobot_uh',
        'bobot_uts',
        'bobot_uas',
    ];

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class);
    }
}

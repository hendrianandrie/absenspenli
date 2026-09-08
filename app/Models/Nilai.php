<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    use HasFactory;

    protected $fillable = [
        'kegiatan_id',
        'siswa_id',
        'nilai',
        'catatan',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}

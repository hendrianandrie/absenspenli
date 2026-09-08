<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'mata_pelajaran_id',
        'kelas',
        'nama_kegiatan',
        'jenis',
        'tanggal',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }
}

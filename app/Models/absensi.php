<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'status',
    ];

    // relasi ke model Siswa (opsional, tapi berguna nanti)
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'nama',
        'kelas',
        'jenis_kelamin',
    ];

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }
}

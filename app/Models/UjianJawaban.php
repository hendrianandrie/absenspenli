<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianJawaban extends Model
{
    use HasFactory;

    protected $fillable = [
        'ujian_siswa_id',
        'soal_id',
        'jawaban',
        'ragu_ragu',
        'is_benar',
        'skor_diperoleh',
        'catatan_koreksi',
    ];

    protected $casts = [
        'ragu_ragu' => 'boolean',
        'is_benar' => 'boolean',
        'skor_diperoleh' => 'float',
    ];

    public function ujianSiswa()
    {
        return $this->belongsTo(UjianSiswa::class);
    }

    public function soal()
    {
        return $this->belongsTo(UjianSoal::class, 'soal_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianSoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'ujian_id',
        'nomor_urut',
        'pertanyaan',
        'gambar',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'kunci_jawaban',
        'bobot',
    ];

    protected $casts = [
        'nomor_urut' => 'integer',
        'bobot' => 'float',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function jawabans()
    {
        return $this->hasMany(UjianJawaban::class, 'soal_id');
    }

    public function getOpsiList(): array
    {
        return [
            'A' => $this->opsi_a,
            'B' => $this->opsi_b,
            'C' => $this->opsi_c,
            'D' => $this->opsi_d,
        ];
    }
}


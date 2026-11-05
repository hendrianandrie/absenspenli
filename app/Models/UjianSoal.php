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
        'gambar_a',
        'opsi_b',
        'gambar_b',
        'opsi_c',
        'gambar_c',
        'opsi_d',
        'gambar_d',
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
            'A' => ['teks' => $this->opsi_a, 'gambar' => $this->gambar_a],
            'B' => ['teks' => $this->opsi_b, 'gambar' => $this->gambar_b],
            'C' => ['teks' => $this->opsi_c, 'gambar' => $this->gambar_c],
            'D' => ['teks' => $this->opsi_d, 'gambar' => $this->gambar_d],
        ];
    }
}

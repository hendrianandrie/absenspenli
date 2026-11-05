<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianSiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'ujian_id',
        'siswa_id',
        'waktu_mulai',
        'waktu_selesai',
        'sisa_waktu_detik',
        'status',
        'jumlah_benar',
        'jumlah_salah',
        'nilai_akhir',
        'urutan_soal',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'sisa_waktu_detik' => 'integer',
        'jumlah_benar' => 'integer',
        'jumlah_salah' => 'integer',
        'nilai_akhir' => 'float',
        'urutan_soal' => 'array',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jawabans()
    {
        return $this->hasMany(UjianJawaban::class, 'ujian_siswa_id');
    }

    /**
     * Hitung hasil nilai ujian berdasarkan kunci jawaban dan bobot soal
     */
    public function hitungHasil(): void
    {
        $soals = $this->ujian->soals->keyBy('id');
        $jawabans = $this->jawabans()->get()->keyBy('soal_id');

        $totalBobot = $soals->sum('bobot') ?: 1;
        $skorDidapat = 0;
        $benar = 0;
        $salah = 0;

        foreach ($soals as $soalId => $soal) {
            $jawabanObj = $jawabans->get($soalId);
            $jawabanUser = $jawabanObj ? strtoupper(trim($jawabanObj->jawaban)) : null;
            $kunci = strtoupper(trim($soal->kunci_jawaban));

            $isBenar = ($jawabanUser !== null && $jawabanUser === $kunci);

            if ($jawabanObj) {
                $jawabanObj->update(['is_benar' => $isBenar]);
            }

            if ($isBenar) {
                $benar++;
                $skorDidapat += (float) $soal->bobot;
            } else {
                $salah++;
            }
        }

        $nilaiSkor100 = round(($skorDidapat / $totalBobot) * 100, 1);

        $this->update([
            'jumlah_benar' => $benar,
            'jumlah_salah' => $salah,
            'nilai_akhir' => min(100, max(0, $nilaiSkor100)),
            'status' => 'selesai',
            'waktu_selesai' => now(),
        ]);
    }
}


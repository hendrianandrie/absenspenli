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
        'status_koreksi',
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
     * Hitung hasil nilai ujian berdasarkan kunci jawaban, pencocokan, dan bobot soal
     */
    public function hitungHasil(): void
    {
        $soals = $this->ujian->soals->keyBy('id');
        $jawabans = $this->jawabans()->get()->keyBy('soal_id');

        $totalBobot = $soals->sum('bobot') ?: 1;
        $skorDidapat = 0;
        $benar = 0;
        $salah = 0;
        $adaEssayBelumDinilai = false;

        foreach ($soals as $soalId => $soal) {
            $jawabanObj = $jawabans->get($soalId);
            $tipe = $soal->tipe_soal ?: 'pg';

            if ($tipe === 'essay') {
                if ($jawabanObj && $jawabanObj->skor_diperoleh !== null) {
                    $skorSoal = min((float) $soal->bobot, max(0, (float) $jawabanObj->skor_diperoleh));
                    $skorDidapat += $skorSoal;
                    if ($skorSoal >= ((float) $soal->bobot * 0.5)) {
                        $benar++;
                    } else {
                        $salah++;
                    }
                } else {
                    $adaEssayBelumDinilai = true;
                }
            } elseif ($tipe === 'menjodohkan') {
                $pairs = ($soal->data_tambahan['pairs'] ?? []);
                $totalPairs = count($pairs);
                $matchedCount = 0;

                if ($jawabanObj && !empty($jawabanObj->jawaban)) {
                    $userPairs = json_decode($jawabanObj->jawaban, true) ?: [];
                    foreach ($pairs as $p) {
                        $pid = (string) ($p['id'] ?? '');
                        $expectedKanan = trim((string) ($p['kanan'] ?? ''));
                        $userKanan = trim((string) ($userPairs[$pid] ?? ''));
                        if ($expectedKanan !== '' && strcasecmp($expectedKanan, $userKanan) === 0) {
                            $matchedCount++;
                        }
                    }
                }

                $ratio = $totalPairs > 0 ? ($matchedCount / $totalPairs) : 0;
                $skorSoal = round($ratio * (float) $soal->bobot, 2);
                $isBenar = ($totalPairs > 0 && $matchedCount === $totalPairs);

                if ($jawabanObj) {
                    $jawabanObj->update([
                        'is_benar' => $isBenar,
                        'skor_diperoleh' => $skorSoal,
                    ]);
                }

                if ($isBenar) {
                    $benar++;
                } else {
                    $salah++;
                }
                $skorDidapat += $skorSoal;
            } else {
                // Tipe Pilihan Ganda (PG)
                $jawabanUser = $jawabanObj ? strtoupper(trim((string) $jawabanObj->jawaban)) : null;
                $kunci = strtoupper(trim((string) $soal->kunci_jawaban));

                $isBenar = ($jawabanUser !== null && $jawabanUser === $kunci);
                $skorSoal = $isBenar ? (float) $soal->bobot : 0;

                if ($jawabanObj) {
                    $jawabanObj->update([
                        'is_benar' => $isBenar,
                        'skor_diperoleh' => $skorSoal,
                    ]);
                }

                if ($isBenar) {
                    $benar++;
                } else {
                    $salah++;
                }
                $skorDidapat += $skorSoal;
            }
        }

        $nilaiSkor100 = round(($skorDidapat / $totalBobot) * 100, 1);
        $statusKoreksi = $adaEssayBelumDinilai ? 'menunggu_koreksi' : 'selesai';

        $this->update([
            'jumlah_benar' => $benar,
            'jumlah_salah' => $salah,
            'nilai_akhir' => min(100, max(0, $nilaiSkor100)),
            'status' => 'selesai',
            'status_koreksi' => $statusKoreksi,
            'waktu_selesai' => $this->waktu_selesai ?: now(),
        ]);
    }

    public function isPerluKoreksi(): bool
    {
        return $this->status_koreksi === 'menunggu_koreksi';
    }
}


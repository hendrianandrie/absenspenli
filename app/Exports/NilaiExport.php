<?php

namespace App\Exports;

use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NilaiExport implements FromView, ShouldAutoSize
{
    protected $kelas;

    protected $mapelId;

    public function __construct($kelas, $mapelId)
    {
        $this->kelas = $kelas;
        $this->mapelId = $mapelId;
    }

    public function view(): View
    {
        $mapel = MataPelajaran::findOrFail($this->mapelId);
        $siswas = Siswa::where('kelas', $this->kelas)->orderBy('nama')->get();
        $kegiatans = Kegiatan::where('mata_pelajaran_id', $this->mapelId)
            ->where('kelas', $this->kelas)
            ->orderBy('tanggal')
            ->get();

        $rekap = [];
        foreach ($siswas as $siswa) {
            $scores = [];
            foreach ($kegiatans as $kegiatan) {
                $n = Nilai::where('kegiatan_id', $kegiatan->id)->where('siswa_id', $siswa->id)->first();
                $scores[$kegiatan->id] = $n ? $n->nilai : null;
            }
            $valid = array_filter($scores, fn ($v) => $v !== null);
            $avg = count($valid) > 0 ? round(array_sum($valid) / count($valid), 1) : null;

            $kkm = $mapel->kkm;
            $predikat = '-';
            if ($avg !== null) {
                if ($avg >= 90) {
                    $predikat = 'A (Sangat Baik)';
                } elseif ($avg >= 80) {
                    $predikat = 'B (Baik)';
                } elseif ($avg >= $kkm) {
                    $predikat = 'C (Cukup / Tuntas)';
                } else {
                    $predikat = 'D (Perlu Bimbingan)';
                }
            }

            $rekap[$siswa->id] = [
                'scores' => $scores,
                'rata_rata' => $avg,
                'predikat' => $predikat,
            ];
        }

        return view('nilai.excel_rekap', [
            'kelas' => $this->kelas,
            'mapel' => $mapel,
            'siswas' => $siswas,
            'kegiatans' => $kegiatans,
            'rekap' => $rekap,
        ]);
    }
}

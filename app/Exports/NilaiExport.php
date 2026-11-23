<?php

namespace App\Exports;

use App\Http\Controllers\NilaiController;
use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NilaiExport implements FromView, ShouldAutoSize
{
    protected $kelas;

    protected $mapelId;

    protected $semester;

    protected $tahunAjaran;

    public function __construct($kelas, $mapelId, $semester = null, $tahunAjaran = null)
    {
        $this->kelas = $kelas;
        $this->mapelId = $mapelId;
        $this->semester = $semester ? (int) $semester : Kegiatan::currentSemester();
        $this->tahunAjaran = $tahunAjaran ?: Kegiatan::currentTahunAjaran();
    }

    public function view(): View
    {
        $mapel = MataPelajaran::findOrFail($this->mapelId);
        $siswas = Siswa::where('kelas', $this->kelas)->orderBy('nama')->get();
        $kegiatans = Kegiatan::where('mata_pelajaran_id', $this->mapelId)
            ->where('kelas', $this->kelas)
            ->where('semester', $this->semester)
            ->where('tahun_ajaran', $this->tahunAjaran)
            ->orderBy('tanggal')
            ->get();

        [$rekap, $analytics] = NilaiController::calculateRekapData($siswas, $kegiatans, $mapel);

        return view('nilai.excel_rekap', [
            'kelas' => $this->kelas,
            'mapel' => $mapel,
            'siswas' => $siswas,
            'kegiatans' => $kegiatans,
            'rekap' => $rekap,
            'analytics' => $analytics,
            'semester' => $this->semester,
            'tahunAjaran' => $this->tahunAjaran,
        ]);
    }
}

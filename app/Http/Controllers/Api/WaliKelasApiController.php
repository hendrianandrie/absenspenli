<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\WaliKelas;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WaliKelasApiController extends Controller
{
    /**
     * Helper mengambil kelas binaan wali kelas
     */
    private function getKelasBinaan(Request $request): ?string
    {
        $user = $request->user();
        if (!$user) return null;

        if ($user->role === 'admin' && $request->filled('kelas')) {
            return $request->kelas;
        }

        $wali = WaliKelas::where('user_id', $user->id)->first();
        return $wali ? $wali->kelas : null;
    }

    /**
     * Nilai Kelas Binaan (Daftar siswa & rata-rata nilai per mata pelajaran)
     */
    public function myClass(Request $request)
    {
        $selectedKelas = $this->getKelasBinaan($request);
        if (!$selectedKelas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda belum ditetapkan sebagai Wali Kelas untuk rombel manapun.'
            ], 403);
        }

        $siswas = Siswa::where('kelas', $selectedKelas)->orderBy('nama')->get();
        $tingkat = MataPelajaran::getTingkatFromKelas($selectedKelas);

        $mapelQuery = MataPelajaran::query();
        if ($tingkat !== 'Semua') {
            $mapelQuery->where(function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat)
                  ->orWhere('tingkat', 'Semua')
                  ->orWhereNull('tingkat');
            });
        }
        $mapels = $mapelQuery->orderBy('nama_mapel')->get();

        $semester = (int) $request->get('semester', Kegiatan::currentSemester());
        $tahunAjaran = $request->get('tahun_ajaran', Kegiatan::currentTahunAjaran());

        $siswaIds = $siswas->pluck('id');
        $kegiatans = Kegiatan::where('kelas', $selectedKelas)
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get();
        $kegiatanMapelMap = $kegiatans->groupBy('mata_pelajaran_id');

        $nilais = Nilai::whereIn('kegiatan_id', $kegiatans->pluck('id'))
            ->whereIn('siswa_id', $siswaIds)
            ->get()
            ->groupBy('siswa_id');

        $rekapSiswa = [];
        foreach ($siswas as $siswa) {
            $siswaNilais = $nilais->get($siswa->id, collect())->keyBy('kegiatan_id');
            $mapelScores = [];
            $allScores = [];

            foreach ($mapels as $mapel) {
                $mapelKegs = $kegiatanMapelMap->get($mapel->id, collect());
                $scores = [];
                foreach ($mapelKegs as $keg) {
                    if ($siswaNilais->has($keg->id)) {
                        $scores[] = (float) $siswaNilais->get($keg->id)->nilai;
                    }
                }

                $avg = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null;
                if ($avg !== null) {
                    $allScores[] = $avg;
                }

                $mapelScores[] = [
                    'mapel_id' => $mapel->id,
                    'nama_mapel' => $mapel->nama_mapel,
                    'kkm' => $mapel->kkm,
                    'rata_rata' => $avg,
                    'is_tuntas' => $avg !== null ? ($avg >= $mapel->kkm) : null,
                ];
            }

            $overallAvg = count($allScores) > 0 ? round(array_sum($allScores) / count($allScores), 1) : null;

            $rekapSiswa[] = [
                'siswa_id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'jenis_kelamin' => $siswa->jenis_kelamin,
                'rata_rata_keseluruhan' => $overallAvg,
                'nilai_mapel' => $mapelScores,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'kelas' => $selectedKelas,
                'semester' => $semester,
                'tahun_ajaran' => $tahunAjaran,
                'total_siswa' => count($siswas),
                'rekap_siswa' => $rekapSiswa,
            ]
        ]);
    }

    /**
     * Rekap Kehadiran Siswa Kelas Binaan
     */
    public function kehadiran(Request $request)
    {
        $selectedKelas = $this->getKelasBinaan($request);
        if (!$selectedKelas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda belum ditetapkan sebagai Wali Kelas untuk rombel manapun.'
            ], 403);
        }

        $now = Carbon::now();
        $bulan = (int) $request->get('bulan', $now->month);
        $tahun = (int) $request->get('tahun', $now->year);

        $siswas = Siswa::where('kelas', $selectedKelas)->orderBy('nama')->get();
        $siswaIds = $siswas->pluck('id');

        $absensiRaw = Absensi::whereIn('siswa_id', $siswaIds)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->selectRaw('siswa_id, status, count(*) as count')
            ->groupBy('siswa_id', 'status')
            ->get();

        $absensiMap = [];
        foreach ($absensiRaw as $ab) {
            $absensiMap[$ab->siswa_id][$ab->status] = $ab->count;
        }

        $rekapKehadiran = $siswas->map(function ($s) use ($absensiMap) {
            $stats = $absensiMap[$s->id] ?? [];
            $hadir = $stats['Hadir'] ?? 0;
            $sakit = $stats['Sakit'] ?? 0;
            $izin = $stats['Izin'] ?? 0;
            $alpha = $stats['Alpha'] ?? 0;
            $total = $hadir + $sakit + $izin + $alpha;

            return [
                'siswa_id' => $s->id,
                'nama' => $s->nama,
                'nis' => $s->nis,
                'hadir' => $hadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpha' => $alpha,
                'total' => $total,
                'persen_hadir' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'kelas' => $selectedKelas,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'total_siswa' => count($siswas),
                'rekap_kehadiran' => $rekapKehadiran,
            ]
        ]);
    }
}

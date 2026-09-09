<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $tanggal = Carbon::today()->toDateString();

        $totalSiswa = Siswa::count();
        $totalMapel = MataPelajaran::count();
        $totalKegiatan = Kegiatan::count();
        $avgNilaiGlobal = Nilai::avg('nilai');

        $kelasList = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $dataKelas = [];

        foreach ($kelasList as $kelas) {
            $jumlahTidakHadir = Absensi::join('siswas', 'absensis.siswa_id', '=', 'siswas.id')
                ->where('siswas.kelas', $kelas)
                ->where('absensis.tanggal', $tanggal)
                ->whereIn('absensis.status', ['Sakit', 'Izin', 'Alpha'])
                ->count();

            $totalSiswaKelas = Siswa::where('kelas', $kelas)->count();
            $jumlahHadir = $totalSiswaKelas - $jumlahTidakHadir;

            $dataKelas[] = [
                'kelas' => $kelas,
                'total' => $totalSiswaKelas,
                'hadir' => $jumlahHadir,
                'tidak_hadir' => $jumlahTidakHadir,
            ];
        }

        $kelasLabels = $dataKelas ? array_column($dataKelas, 'kelas') : [];
        $hadirData = $dataKelas ? array_column($dataKelas, 'hadir') : [];
        $tidakHadirData = $dataKelas ? array_column($dataKelas, 'tidak_hadir') : [];

        // Calculation for Absensi Percentages (Hari Ini, Weekly, Monthly, Overall)
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek()->toDateString();
        $endOfWeek = $now->copy()->endOfWeek()->toDateString();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $calcStats = function ($startDate = null, $endDate = null) {
            $query = Absensi::query();
            if ($startDate && $endDate) {
                if ($startDate === $endDate) {
                    $query->where('tanggal', $startDate);
                } else {
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                }
            }

            $total = $query->count();

            if ($total === 0) {
                return [
                    'total' => 0,
                    'hadir' => 0, 'hadir_pct' => 0,
                    'sakit' => 0, 'sakit_pct' => 0,
                    'izin' => 0, 'izin_pct' => 0,
                    'alpha' => 0, 'alpha_pct' => 0,
                ];
            }

            $counts = $query->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            $hadir = $counts['Hadir'] ?? 0;
            $sakit = $counts['Sakit'] ?? 0;
            $izin = $counts['Izin'] ?? 0;
            $alpha = $counts['Alpha'] ?? 0;

            return [
                'total' => $total,
                'hadir' => $hadir,
                'hadir_pct' => round(($hadir / $total) * 100, 1),
                'sakit' => $sakit,
                'sakit_pct' => round(($sakit / $total) * 100, 1),
                'izin' => $izin,
                'izin_pct' => round(($izin / $total) * 100, 1),
                'alpha' => $alpha,
                'alpha_pct' => round(($alpha / $total) * 100, 1),
            ];
        };

        $statsHariIni = $calcStats($tanggal, $tanggal);
        $statsMingguIni = $calcStats($startOfWeek, $endOfWeek);
        $statsBulanIni = $calcStats($startOfMonth, $endOfMonth);
        $statsKeseluruhan = $calcStats();

        return view('dashboard', compact(
            'dataKelas',
            'tanggal',
            'kelasLabels',
            'hadirData',
            'tidakHadirData',
            'totalSiswa',
            'totalMapel',
            'totalKegiatan',
            'avgNilaiGlobal',
            'statsHariIni',
            'statsMingguIni',
            'statsBulanIni',
            'statsKeseluruhan'
        ));
    }

    public function cetakPdf()
    {
        $tanggal = now()->toDateString();

        $kelasList = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $dataKelas = [];

        $totalGlobalSiswa = 0;
        $totalGlobalHadir = 0;
        $totalGlobalSakit = 0;
        $totalGlobalIzin = 0;
        $totalGlobalAlpha = 0;
        $totalGlobalTidakHadir = 0;

        foreach ($kelasList as $kelas) {
            $counts = Absensi::join('siswas', 'absensis.siswa_id', '=', 'siswas.id')
                ->where('siswas.kelas', $kelas)
                ->where('absensis.tanggal', $tanggal)
                ->selectRaw('absensis.status, COUNT(*) as aggregate')
                ->groupBy('absensis.status')
                ->pluck('aggregate', 'absensis.status');

            $sakit = $counts['Sakit'] ?? 0;
            $izin = $counts['Izin'] ?? 0;
            $alpha = $counts['Alpha'] ?? 0;
            $jumlahTidakHadir = $sakit + $izin + $alpha;

            $totalSiswa = Siswa::where('kelas', $kelas)->count();
            $jumlahHadir = max(0, $totalSiswa - $jumlahTidakHadir);
            $persenHadir = $totalSiswa > 0 ? round(($jumlahHadir / $totalSiswa) * 100, 1) : 0;

            $totalGlobalSiswa += $totalSiswa;
            $totalGlobalHadir += $jumlahHadir;
            $totalGlobalSakit += $sakit;
            $totalGlobalIzin += $izin;
            $totalGlobalAlpha += $alpha;
            $totalGlobalTidakHadir += $jumlahTidakHadir;

            $dataKelas[] = [
                'kelas' => $kelas,
                'total' => $totalSiswa,
                'hadir' => $jumlahHadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpha' => $alpha,
                'tidak_hadir' => $jumlahTidakHadir,
                'persen' => $persenHadir,
            ];
        }

        $totalGlobalPersen = $totalGlobalSiswa > 0 ? round(($totalGlobalHadir / $totalGlobalSiswa) * 100, 1) : 0;

        $summary = [
            'total_siswa' => $totalGlobalSiswa,
            'hadir' => $totalGlobalHadir,
            'sakit' => $totalGlobalSakit,
            'izin' => $totalGlobalIzin,
            'alpha' => $totalGlobalAlpha,
            'tidak_hadir' => $totalGlobalTidakHadir,
            'persen' => $totalGlobalPersen,
        ];

        $pdf = Pdf::loadView('dashboard_pdf', compact('dataKelas', 'tanggal', 'summary'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('rekap_kehadiran_'.$tanggal.'.pdf');
    }
}

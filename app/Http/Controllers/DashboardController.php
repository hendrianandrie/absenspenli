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

        $user = auth()->user();
        $isGuru = $user && $user->role === 'guru';

        $guruMapel = null;
        $guruKelas = [];
        $guruDaftarKelas = collect();
        $selectedGuruKelas = '';
        $guruKegiatans = collect();
        $guruStats = [];
        $guruSiswasWithAbsen = collect();
        $guruAbsenStats = [
            'total' => 0,
            'hadir' => 0,
            'sakit' => 0,
            'izin' => 0,
            'alpha' => 0,
            'tidak_hadir' => 0,
            'hadir_pct' => 0,
        ];

        if ($isGuru) {
            $guruMapel = $user->mataPelajaran;
            $guruKelas = is_array($user->kelas_diampu) ? $user->kelas_diampu : [];

            if (! empty($guruKelas)) {
                $guruDaftarKelas = collect($guruKelas)->sort()->values();
            } else {
                $guruDaftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
            }
            $selectedGuruKelas = request()->get('kelas_guru', $guruDaftarKelas->first() ?? '');
            if ($selectedGuruKelas && ! $guruDaftarKelas->contains($selectedGuruKelas)) {
                $selectedGuruKelas = $guruDaftarKelas->first() ?? '';
            }

            if ($guruMapel) {
                // Fetch activities for selected class & subject
                $guruKegiatans = Kegiatan::where('mata_pelajaran_id', $guruMapel->id)
                    ->when($selectedGuruKelas, fn ($q) => $q->where('kelas', $selectedGuruKelas))
                    ->orderBy('tanggal', 'desc')
                    ->get();

                // Total students in assigned classes
                $totalSiswaDiampu = Siswa::when(! empty($guruKelas), fn ($q) => $q->whereIn('kelas', $guruKelas))->count();
                $totalKegiatanMapel = Kegiatan::where('mata_pelajaran_id', $guruMapel->id)
                    ->when(! empty($guruKelas), fn ($q) => $q->whereIn('kelas', $guruKelas))
                    ->count();

                $kegiatanIds = Kegiatan::where('mata_pelajaran_id', $guruMapel->id)
                    ->when(! empty($guruKelas), fn ($q) => $q->whereIn('kelas', $guruKelas))
                    ->pluck('id');

                $avgNilaiMapel = Nilai::whereIn('kegiatan_id', $kegiatanIds)->avg('nilai');
                $nilaisAll = Nilai::whereIn('kegiatan_id', $kegiatanIds)->pluck('nilai');
                $tuntasCount = $nilaisAll->filter(fn ($v) => $v >= $guruMapel->kkm)->count();
                $totalNilaiEntry = $nilaisAll->count();
                $pctTuntas = $totalNilaiEntry > 0 ? round(($tuntasCount / $totalNilaiEntry) * 100, 1) : 0;

                $guruStats = [
                    'total_siswa' => $totalSiswaDiampu,
                    'total_kegiatan' => $totalKegiatanMapel,
                    'avg_nilai' => $avgNilaiMapel ? round($avgNilaiMapel, 1) : 0,
                    'pct_tuntas' => $pctTuntas,
                    'tuntas_count' => $tuntasCount,
                    'belum_tuntas_count' => $totalNilaiEntry - $tuntasCount,
                ];

                // Ambil Data Siswa & Absensi Hari Ini untuk Kelas Terpilih
                if ($selectedGuruKelas) {
                    $siswasInClass = Siswa::where('kelas', $selectedGuruKelas)->orderBy('nama')->get();

                    $absensiToday = Absensi::join('siswas', 'absensis.siswa_id', '=', 'siswas.id')
                        ->where('siswas.kelas', $selectedGuruKelas)
                        ->where('absensis.tanggal', $tanggal)
                        ->pluck('absensis.status', 'siswas.id');

                    $hadirCount = 0;
                    $sakitCount = 0;
                    $izinCount = 0;
                    $alphaCount = 0;

                    $guruSiswasWithAbsen = $siswasInClass->map(function ($s) use ($absensiToday, $guruMapel, $selectedGuruKelas, &$hadirCount, &$sakitCount, &$izinCount, &$alphaCount) {
                        $st = $absensiToday[$s->id] ?? 'Hadir'; // Default Hadir jika belum diisi absensi
                        if ($st === 'Sakit') $sakitCount++;
                        elseif ($st === 'Izin') $izinCount++;
                        elseif ($st === 'Alpha') $alphaCount++;
                        else $hadirCount++;

                        // Nilai siswa di mapel ini
                        $kegiatanIdsClass = Kegiatan::where('mata_pelajaran_id', $guruMapel->id)
                            ->where('kelas', $selectedGuruKelas)
                            ->pluck('id');

                        $scores = Nilai::whereIn('kegiatan_id', $kegiatanIdsClass)
                            ->where('siswa_id', $s->id)
                            ->pluck('nilai');

                        $avg = $scores->count() > 0 ? round($scores->avg(), 1) : null;
                        $kkm = $guruMapel->kkm ?? 75;

                        return [
                            'siswa' => $s,
                            'status_absen' => $st,
                            'nilai_akhir' => $avg,
                            'is_tuntas' => $avg !== null ? ($avg >= $kkm) : null,
                        ];
                    });

                    $tot = count($siswasInClass);
                    $tidakHadirCount = $sakitCount + $izinCount + $alphaCount;
                    $guruAbsenStats = [
                        'total' => $tot,
                        'hadir' => $hadirCount,
                        'sakit' => $sakitCount,
                        'izin' => $izinCount,
                        'alpha' => $alphaCount,
                        'tidak_hadir' => $tidakHadirCount,
                        'hadir_pct' => $tot > 0 ? round(($hadirCount / $tot) * 100, 1) : 0,
                    ];
                }
            }
        }

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
            'statsKeseluruhan',
            'isGuru',
            'guruMapel',
            'guruKelas',
            'guruDaftarKelas',
            'selectedGuruKelas',
            'guruKegiatans',
            'guruStats',
            'guruSiswasWithAbsen',
            'guruAbsenStats'
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

<?php

namespace App\Http\Controllers\Perpus;

use App\Http\Controllers\Controller;
use App\Models\PerpusPeminjaman;
use App\Models\PerpusPoinLog;
use App\Models\PerpusResume;
use App\Models\Siswa;
use App\Models\WaliKelas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Tampilkan Papan Peringkat Literasi Siswa & Kelas
     */
    public function index(Request $request)
    {
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');

        $selectedBulan = $request->get('bulan', $currentMonth);
        $selectedTahun = (int) $request->get('tahun', $currentYear);
        $filterMode = $request->get('mode', 'bulanan'); // 'bulanan' atau 'all'

        $daftarBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // 1. LEADERBOARD SISWA
        $siswaQuery = PerpusPoinLog::select(
                'siswa_id',
                DB::raw('SUM(poin) as total_poin'),
                DB::raw('COUNT(id) as total_aktivitas')
            )
            ->groupBy('siswa_id');

        if ($filterMode === 'bulanan' && $selectedBulan !== 'all') {
            $siswaQuery->where('bulan', $selectedBulan)->where('tahun', $selectedTahun);
        }

        $topSiswaPoin = $siswaQuery->orderBy('total_poin', 'desc')
            ->limit(20)
            ->get();

        $siswaIds = $topSiswaPoin->pluck('siswa_id');
        $siswasMap = Siswa::whereIn('id', $siswaIds)->get()->keyBy('id');

        // Tambahan data statistik buku & resume per siswa
        $leaderboardSiswa = [];
        foreach ($topSiswaPoin as $idx => $sp) {
            $siswa = $siswasMap->get($sp->siswa_id);
            if (!$siswa) continue;

            $bukuCount = PerpusPeminjaman::where('siswa_id', $siswa->id);
            $resumeCount = PerpusResume::where('siswa_id', $siswa->id)->where('status', 'disetujui');

            if ($filterMode === 'bulanan' && $selectedBulan !== 'all') {
                $bukuCount->whereYear('tanggal_pinjam', $selectedTahun)->whereMonth('tanggal_pinjam', $selectedBulan);
                $resumeCount->whereYear('created_at', $selectedTahun)->whereMonth('created_at', $selectedBulan);
            }

            $pts = (int) $sp->total_poin;
            $badge = self::getBadgeByPoin($pts);

            $leaderboardSiswa[] = [
                'rank' => $idx + 1,
                'siswa' => $siswa,
                'total_poin' => $pts,
                'total_buku' => $bukuCount->count(),
                'total_resume' => $resumeCount->count(),
                'badge' => $badge,
            ];
        }

        // 2. LEADERBOARD KELAS TERAKTIF (BERBASIS INDEKS RATA-RATA POIN PER SISWA)
        $allSiswas = Siswa::all()->groupBy('kelas');
        $waliKelasMap = WaliKelas::with('user')->get()->keyBy('kelas');

        $kelasStats = [];
        foreach ($allSiswas as $kelasName => $students) {
            $totalStudents = $students->count();
            if ($totalStudents === 0) continue;

            $studentIds = $students->pluck('id');

            $poinLogQuery = PerpusPoinLog::whereIn('siswa_id', $studentIds);
            $bukuQuery = PerpusPeminjaman::whereIn('siswa_id', $studentIds);

            if ($filterMode === 'bulanan' && $selectedBulan !== 'all') {
                $poinLogQuery->where('bulan', $selectedBulan)->where('tahun', $selectedTahun);
                $bukuQuery->whereYear('tanggal_pinjam', $selectedTahun)->whereMonth('tanggal_pinjam', $selectedBulan);
            }

            $totalClassPoin = (int) $poinLogQuery->sum('poin');
            $activeStudentCount = $poinLogQuery->distinct('siswa_id')->count('siswa_id');
            $totalBukuPinjam = $bukuQuery->count();

            $avgPoinPerSiswa = $totalStudents > 0 ? round($totalClassPoin / $totalStudents, 1) : 0;
            $partisipasiPct = $totalStudents > 0 ? round(($activeStudentCount / $totalStudents) * 100, 1) : 0;

            $wali = $waliKelasMap->get($kelasName);
            $namaWali = optional(optional($wali)->user)->name ?? 'Belum Ditentukan';

            $kelasStats[] = [
                'kelas' => $kelasName,
                'wali_kelas' => $namaWali,
                'total_siswa' => $totalStudents,
                'active_siswa' => $activeStudentCount,
                'partisipasi_pct' => $partisipasiPct,
                'total_buku' => $totalBukuPinjam,
                'total_poin' => $totalClassPoin,
                'indeks_rata_rata' => $avgPoinPerSiswa,
            ];
        }

        // Urutkan kelas berdasarkan Indeks Rata-Rata Poin Tertinggi
        usort($kelasStats, function ($a, $b) {
            if ($b['indeks_rata_rata'] === $a['indeks_rata_rata']) {
                return $b['total_poin'] <=> $a['total_poin'];
            }
            return $b['indeks_rata_rata'] <=> $a['indeks_rata_rata'];
        });

        // Beri ranking kelas
        foreach ($kelasStats as $i => &$ks) {
            $ks['rank'] = $i + 1;
        }

        // 3. RIWAYAT JUARA BULANAN (HALL OF FAME SEPANJANG TAHUN)
        $hallOfFameBulanan = self::getMonthlyHallOfFame($selectedTahun);

        return view('perpus.leaderboard.index', compact(
            'leaderboardSiswa',
            'kelasStats',
            'hallOfFameBulanan',
            'selectedBulan',
            'selectedTahun',
            'filterMode',
            'daftarBulan'
        ));
    }

    /**
     * Hitung Riwayat Juara 1 Siswa & Kelas per Bulan (Hall of Fame)
     */
    private static function getMonthlyHallOfFame(int $year): array
    {
        $months = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6]; // Urutan tahun ajaran
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $hallOfFame = [];
        $currentMonth = (int) date('n');

        foreach ($months as $m) {
            // Ambil Top Siswa di bulan ini
            $topSiswaLog = PerpusPoinLog::select('siswa_id', DB::raw('SUM(poin) as total_poin'))
                ->where('bulan', $m)
                ->where('tahun', $year)
                ->groupBy('siswa_id')
                ->orderBy('total_poin', 'desc')
                ->first();

            $topSiswa = null;
            if ($topSiswaLog && $topSiswaLog->total_poin > 0) {
                $s = Siswa::find($topSiswaLog->siswa_id);
                if ($s) {
                    $topSiswa = [
                        'nama' => $s->nama,
                        'kelas' => $s->kelas,
                        'poin' => (int) $topSiswaLog->total_poin,
                    ];
                }
            }

            // Ambil Top Kelas di bulan ini
            $topKelas = null;
            $allSiswas = Siswa::all()->groupBy('kelas');
            $highestAvg = 0;
            $bestClass = null;

            foreach ($allSiswas as $cName => $students) {
                $count = $students->count();
                if ($count === 0) continue;
                $pSum = PerpusPoinLog::whereIn('siswa_id', $students->pluck('id'))
                    ->where('bulan', $m)
                    ->where('tahun', $year)
                    ->sum('poin');

                $avg = round($pSum / $count, 1);
                if ($avg > $highestAvg && $pSum > 0) {
                    $highestAvg = $avg;
                    $bestClass = [
                        'kelas' => $cName,
                        'indeks' => $avg,
                        'total_poin' => (int) $pSum,
                    ];
                }
            }

            $hallOfFame[] = [
                'bulan_angka' => $m,
                'nama_bulan' => $bulanNames[$m],
                'is_current' => ($m === $currentMonth),
                'top_siswa' => $topSiswa,
                'top_kelas' => $bestClass,
            ];
        }

        return $hallOfFame;
    }

    /**
     * Helper Badge Poin
     */
    public static function getBadgeByPoin(int $poin): array
    {
        if ($poin >= 300) {
            return ['nama' => 'Duta Baca Emas', 'class' => 'bg-warning-subtle text-warning border-warning', 'icon' => 'fa-crown'];
        } elseif ($poin >= 150) {
            return ['nama' => 'Sahabat Literasi', 'class' => 'bg-primary-subtle text-primary border-primary', 'icon' => 'fa-medal'];
        } elseif ($poin >= 50) {
            return ['nama' => 'Kutu Buku Aktif', 'class' => 'bg-info-subtle text-info border-info', 'icon' => 'fa-book-open-reader'];
        } else {
            return ['nama' => 'Pembaca Pemula', 'class' => 'bg-secondary-subtle text-secondary border-secondary', 'icon' => 'fa-seedling'];
        }
    }
}

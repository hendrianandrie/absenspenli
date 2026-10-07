<?php

namespace App\Http\Controllers;

use App\Exports\LegerNilaiExport;
use App\Exports\RekapKehadiranKelasExport;
use App\Models\Absensi;
use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WaliKelasController extends Controller
{
    /**
     * Admin: Pengaturan Wali Kelas untuk semua rombel
     */
    public function index()
    {
        // Urutkan kelas secara logis (VII, VIII, IX)
        $order = ['VII' => 1, 'VIII' => 2, 'IX' => 3];
        $rawKelas = Siswa::select('kelas')->distinct()->pluck('kelas');
        $daftarKelas = $rawKelas->sort(function ($a, $b) use ($order) {
            $tA = $order[strtoupper(MataPelajaran::getTingkatFromKelas($a))] ?? 99;
            $tB = $order[strtoupper(MataPelajaran::getTingkatFromKelas($b))] ?? 99;
            if ($tA !== $tB) return $tA <=> $tB;
            return strnatcasecmp($a, $b);
        })->values();

        $waliMap = WaliKelas::with('user')->get()->keyBy('kelas');
        $siswaCounts = Siswa::selectRaw('kelas, count(*) as count')->groupBy('kelas')->pluck('count', 'kelas');
        $users = User::orderBy('name')->get();

        $totalKelas = $daftarKelas->count();
        $assignedCount = $waliMap->filter(fn ($w) => !empty($w->user_id))->count();
        $unassignedCount = $totalKelas - $assignedCount;

        return view('walikelas.index', compact(
            'daftarKelas',
            'waliMap',
            'siswaCounts',
            'users',
            'totalKelas',
            'assignedCount',
            'unassignedCount'
        ));
    }

    /**
     * Admin: Simpan atau perbarui penetapan wali kelas
     */
    public function assign(Request $request)
    {
        $request->validate([
            'kelas' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $kelas = $request->kelas;
        $userId = $request->user_id ?: null;

        if ($userId) {
            // Jika akun ini sudah jadi wali kelas di rombel lain, lepas dari kelas sebelumnya atau beri opsi
            WaliKelas::where('user_id', $userId)->where('kelas', '!=', $kelas)->update(['user_id' => null]);

            WaliKelas::updateOrCreate(
                ['kelas' => $kelas],
                [
                    'user_id' => $userId,
                    'tahun_ajaran' => date('Y') . '/' . (date('Y') + 1)
                ]
            );
            $user = User::find($userId);
            return redirect()->back()->with('success', "Wali Kelas untuk {$kelas} berhasil ditetapkan kepada {$user->name}!");
        } else {
            WaliKelas::where('kelas', $kelas)->update(['user_id' => null]);
            return redirect()->back()->with('success', "Wali Kelas untuk {$kelas} telah dikosongkan.");
        }
    }

    /**
     * Wali Kelas / Admin: Melihat data nilai & progres akademik siswa di kelasnya
     */
    public function myClass(Request $request)
    {
        $currentUser = auth()->user();
        $selectedKelas = null;
        $waliKelas = null;

        if ($currentUser && $currentUser->role === 'admin' && $request->filled('kelas')) {
            $selectedKelas = $request->kelas;
            $waliKelas = WaliKelas::where('kelas', $selectedKelas)->with('user')->first();
        } else {
            $waliKelas = $currentUser->waliKelas()->with('user')->first();
            $selectedKelas = $waliKelas ? $waliKelas->kelas : null;
        }

        if (!$selectedKelas) {
            return redirect()->route('dashboard')->with('error', 'Anda belum ditetapkan sebagai Wali Kelas untuk rombel manapun.');
        }

        $data = $this->getRekapDataForClass($selectedKelas);

        return view('walikelas.show', array_merge([
            'selectedKelas' => $selectedKelas,
            'waliKelas' => $waliKelas,
        ], $data));
    }

    /**
     * Download Rekapitulasi Nilai Siswa Per Mata Pelajaran (Leger Nilai) dalam format Excel
     */
    public function exportExcel(Request $request)
    {
        $currentUser = auth()->user();
        $selectedKelas = null;
        $waliKelas = null;

        if ($currentUser && $currentUser->role === 'admin' && $request->filled('kelas')) {
            $selectedKelas = $request->kelas;
            $waliKelas = WaliKelas::where('kelas', $selectedKelas)->with('user')->first();
        } else {
            $waliKelas = $currentUser ? $currentUser->waliKelas()->with('user')->first() : null;
            $selectedKelas = $waliKelas ? $waliKelas->kelas : $request->get('kelas');
        }

        if (!$selectedKelas) {
            return redirect()->back()->with('error', 'Kelas belum ditentukan atau Anda belum ditetapkan sebagai Wali Kelas.');
        }

        if (!$waliKelas) {
            $waliKelas = WaliKelas::where('kelas', $selectedKelas)->with('user')->first();
        }

        $data = $this->getRekapDataForClass($selectedKelas);
        $fileName = 'Rekapitulasi_Nilai_Siswa_Per_Mapel_Kelas_' . str_replace([' ', '/', '\\'], '_', $selectedKelas) . '.xlsx';

        return Excel::download(
            new LegerNilaiExport($selectedKelas, $waliKelas, $data),
            $fileName
        );
    }

    /**
     * Wali Kelas / Admin: Melihat rekapitulasi kehadiran / presensi bulanan siswa kelas binaan
     */
    public function kehadiranKelas(Request $request)
    {
        $currentUser = auth()->user();
        $selectedKelas = null;
        $waliKelas = null;

        if ($currentUser && $currentUser->role === 'admin' && $request->filled('kelas')) {
            $selectedKelas = $request->kelas;
            $waliKelas = WaliKelas::where('kelas', $selectedKelas)->with('user')->first();
        } else {
            $waliKelas = $currentUser->waliKelas()->with('user')->first();
            $selectedKelas = $waliKelas ? $waliKelas->kelas : ($currentUser->role === 'admin' ? $request->get('kelas', 'VII A') : null);
        }

        if (!$selectedKelas) {
            return redirect()->route('dashboard')->with('error', 'Anda belum ditetapkan sebagai Wali Kelas untuk rombel manapun.');
        }

        if (!$waliKelas) {
            $waliKelas = WaliKelas::where('kelas', $selectedKelas)->with('user')->first();
        }

        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        $data = $this->getRekapKehadiranData($selectedKelas, $bulan, $tahun);

        // Daftar nama bulan
        $daftarBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $daftarTahun = range(date('Y') - 2, date('Y') + 1);

        // Jika admin, sediakan daftar semua kelas untuk opsi ganti rombel
        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        return view('walikelas.kehadiran', array_merge([
            'selectedKelas' => $selectedKelas,
            'waliKelas' => $waliKelas,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'daftarBulan' => $daftarBulan,
            'daftarTahun' => $daftarTahun,
            'daftarKelas' => $daftarKelas,
        ], $data));
    }

    /**
     * Download Rekapitulasi Kehadiran Siswa Kelas Binaan dalam format Excel
     */
    public function exportKehadiranExcel(Request $request)
    {
        $currentUser = auth()->user();
        $selectedKelas = null;
        $waliKelas = null;

        if ($currentUser && $currentUser->role === 'admin' && $request->filled('kelas')) {
            $selectedKelas = $request->kelas;
            $waliKelas = WaliKelas::where('kelas', $selectedKelas)->with('user')->first();
        } else {
            $waliKelas = $currentUser ? $currentUser->waliKelas()->with('user')->first() : null;
            $selectedKelas = $waliKelas ? $waliKelas->kelas : $request->get('kelas');
        }

        if (!$selectedKelas) {
            return redirect()->back()->with('error', 'Kelas belum ditentukan atau Anda bukan Wali Kelas.');
        }

        if (!$waliKelas) {
            $waliKelas = WaliKelas::where('kelas', $selectedKelas)->with('user')->first();
        }

        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        $daftarBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $data = $this->getRekapKehadiranData($selectedKelas, $bulan, $tahun);
        $namaBulan = $daftarBulan[$bulan] ?? 'Bulan_' . $bulan;
        $fileName = 'Rekap_Kehadiran_Kelas_' . str_replace([' ', '/', '\\'], '_', $selectedKelas) . '_' . $namaBulan . '_' . $tahun . '.xlsx';

        return Excel::download(
            new RekapKehadiranKelasExport($selectedKelas, $waliKelas, $bulan, $tahun, $namaBulan, $data),
            $fileName
        );
    }

    /**
     * Helper: Menghitung rekapitulasi kehadiran per siswa di kelas tertentu berdasarkan bulan & tahun
     */
    protected function getRekapKehadiranData(string $selectedKelas, int $bulan, int $tahun): array
    {
        $siswas = Siswa::where('kelas', $selectedKelas)->orderBy('nama')->get();
        $siswaIds = $siswas->pluck('id');

        $absensis = Absensi::whereIn('siswa_id', $siswaIds)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get();

        // Hitung tanggal unik presensi diambil di kelas ini pada bulan tersebut
        $distinctDates = $absensis->pluck('tanggal')->unique();
        $totalHariEfektif = $distinctDates->count();

        // Kelompokkan per siswa_id
        $absensiGrouped = $absensis->groupBy('siswa_id');

        $rekapSiswa = [];
        $totalKelasHadir = 0;
        $totalKelasSakit = 0;
        $totalKelasIzin = 0;
        $totalKelasAlpha = 0;
        $persenAccum = [];

        foreach ($siswas as $siswa) {
            $records = $absensiGrouped->get($siswa->id, collect());
            $hadir = $records->where('status', 'Hadir')->count();
            $sakit = $records->where('status', 'Sakit')->count();
            $izin = $records->where('status', 'Izin')->count();
            $alpha = $records->where('status', 'Alpha')->count();
            $totalTercatat = $hadir + $sakit + $izin + $alpha;

            // Persentase kehadiran siswa
            $persen = $totalTercatat > 0 ? round(($hadir / $totalTercatat) * 100, 1) : 100.0;
            $persenAccum[] = $persen;

            $totalKelasHadir += $hadir;
            $totalKelasSakit += $sakit;
            $totalKelasIzin += $izin;
            $totalKelasAlpha += $alpha;

            $rekapSiswa[$siswa->id] = [
                'siswa' => $siswa,
                'hadir' => $hadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpha' => $alpha,
                'total' => $totalTercatat,
                'persen' => $persen,
            ];
        }

        $totalSiswa = $siswas->count();
        $avgPersenKelas = count($persenAccum) > 0 ? round(array_sum($persenAccum) / count($persenAccum), 1) : 100.0;

        $analytics = [
            'total_siswa' => $totalSiswa,
            'total_hari_efektif' => $totalHariEfektif,
            'total_hadir' => $totalKelasHadir,
            'total_sakit' => $totalKelasSakit,
            'total_izin' => $totalKelasIzin,
            'total_alpha' => $totalKelasAlpha,
            'avg_persen' => $avgPersenKelas,
        ];

        return compact('siswas', 'rekapSiswa', 'analytics');
    }

    /**
     * Helper: Menghitung rekapitulasi nilai berbobot dan absensi per siswa di kelas tertentu
     */
    protected function getRekapDataForClass(string $selectedKelas): array
    {
        $siswas = Siswa::where('kelas', $selectedKelas)->orderBy('nama')->get();
        $tingkat = MataPelajaran::getTingkatFromKelas($selectedKelas);

        // Filter mapel yang relevan untuk tingkat kelas ini
        $mapelQuery = MataPelajaran::query();
        if ($tingkat !== 'Semua') {
            $mapelQuery->where(function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat)
                  ->orWhere('tingkat', 'Semua')
                  ->orWhereNull('tingkat');
            });
        }
        $mapels = $mapelQuery->orderBy('nama_mapel')->get();

        // Ambil rekap absensi seluruh siswa kelas ini
        $siswaIds = $siswas->pluck('id');
        $absensiRaw = Absensi::whereIn('siswa_id', $siswaIds)
            ->selectRaw('siswa_id, status, count(*) as count')
            ->groupBy('siswa_id', 'status')
            ->get();

        $absensiMap = [];
        foreach ($absensiRaw as $ab) {
            $absensiMap[$ab->siswa_id][$ab->status] = $ab->count;
        }

        // Ambil semua kegiatan penilaian untuk kelas ini
        $kegiatans = Kegiatan::where('kelas', $selectedKelas)->get();
        $kegiatanIds = $kegiatans->pluck('id');
        $kegiatanMapelMap = $kegiatans->groupBy('mata_pelajaran_id');

        // Ambil semua nilai
        $nilais = Nilai::whereIn('kegiatan_id', $kegiatanIds)
            ->whereIn('siswa_id', $siswaIds)
            ->get()
            ->groupBy('siswa_id');

        $rekapSiswa = [];
        $classScoresAccum = [];

        foreach ($siswas as $siswa) {
            $siswaNilais = $nilais->get($siswa->id, collect())->keyBy('kegiatan_id');
            $mapelScores = [];
            $validFinalScores = [];
            $tuntasMapelCount = 0;
            $totalAssessedMapel = 0;

            foreach ($mapels as $mapel) {
                $mapelKegs = $kegiatanMapelMap->get($mapel->id, collect());
                if ($mapelKegs->isEmpty()) {
                    $mapelScores[$mapel->id] = null;
                    continue;
                }

                $bTugas = $mapel->bobot_tugas ?? 20;
                $bUh = $mapel->bobot_uh ?? 30;
                $bUts = $mapel->bobot_uts ?? 25;
                $bUas = $mapel->bobot_uas ?? 25;

                $scoresByJenis = ['Tugas' => [], 'UH' => [], 'UTS' => [], 'UAS' => []];
                $allRawScores = [];

                foreach ($mapelKegs as $keg) {
                    $val = $siswaNilais->has($keg->id) ? $siswaNilais->get($keg->id)->nilai : null;
                    if ($val !== null) {
                        $allRawScores[] = $val;
                        if (isset($scoresByJenis[$keg->jenis])) {
                            $scoresByJenis[$keg->jenis][] = $val;
                        }
                    }
                }

                if (empty($allRawScores)) {
                    $mapelScores[$mapel->id] = null;
                    continue;
                }

                $avgTugas = count($scoresByJenis['Tugas']) > 0 ? array_sum($scoresByJenis['Tugas']) / count($scoresByJenis['Tugas']) : null;
                $avgUh = count($scoresByJenis['UH']) > 0 ? array_sum($scoresByJenis['UH']) / count($scoresByJenis['UH']) : null;
                $avgUts = count($scoresByJenis['UTS']) > 0 ? array_sum($scoresByJenis['UTS']) / count($scoresByJenis['UTS']) : null;
                $avgUas = count($scoresByJenis['UAS']) > 0 ? array_sum($scoresByJenis['UAS']) / count($scoresByJenis['UAS']) : null;

                $weightedSum = 0;
                $weightTotal = 0;
                if ($avgTugas !== null) { $weightedSum += ($avgTugas * $bTugas); $weightTotal += $bTugas; }
                if ($avgUh !== null) { $weightedSum += ($avgUh * $bUh); $weightTotal += $bUh; }
                if ($avgUts !== null) { $weightedSum += ($avgUts * $bUts); $weightTotal += $bUts; }
                if ($avgUas !== null) { $weightedSum += ($avgUas * $bUas); $weightTotal += $bUas; }

                $final = $weightTotal > 0 ? round($weightedSum / $weightTotal, 1) : round(array_sum($allRawScores) / count($allRawScores), 1);
                $mapelScores[$mapel->id] = $final;
                $validFinalScores[] = $final;
                $totalAssessedMapel++;

                if ($final >= ($mapel->kkm ?? 75)) {
                    $tuntasMapelCount++;
                }
            }

            $overallAvg = count($validFinalScores) > 0 ? round(array_sum($validFinalScores) / count($validFinalScores), 1) : null;
            if ($overallAvg !== null) {
                $classScoresAccum[] = $overallAvg;
            }

            $absen = $absensiMap[$siswa->id] ?? [];
            $rekapSiswa[$siswa->id] = [
                'scores' => $mapelScores,
                'total_nilai' => count($validFinalScores) > 0 ? round(array_sum($validFinalScores), 1) : 0,
                'overall_avg' => $overallAvg,
                'tuntas_mapel' => $tuntasMapelCount,
                'total_mapel' => $totalAssessedMapel,
                'hadir' => $absen['Hadir'] ?? 0,
                'sakit' => $absen['Sakit'] ?? 0,
                'izin' => $absen['Izin'] ?? 0,
                'alpha' => $absen['Alpha'] ?? 0,
            ];
        }

        $analytics = [
            'total_siswa' => $siswas->count(),
            'class_avg' => count($classScoresAccum) > 0 ? round(array_sum($classScoresAccum) / count($classScoresAccum), 1) : 0,
            'highest' => count($classScoresAccum) > 0 ? max($classScoresAccum) : 0,
            'lowest' => count($classScoresAccum) > 0 ? min($classScoresAccum) : 0,
        ];

        return compact(
            'siswas',
            'mapels',
            'rekapSiswa',
            'analytics',
            'tingkat'
        );
    }
}

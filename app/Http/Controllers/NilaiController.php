<?php

namespace App\Http\Controllers;

use App\Exports\NilaiExport;
use App\Models\Kegiatan;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isGuru = $user && $user->role === 'guru';

        // Filter Mapel & Kelas jika Role Guru
        if ($isGuru && $user->mata_pelajaran_id) {
            $mapels = MataPelajaran::where('id', $user->mata_pelajaran_id)->get();
        } else {
            $mapels = MataPelajaran::orderBy('nama_mapel')->get();
        }

        if ($isGuru && ! empty($user->kelas_diampu) && is_array($user->kelas_diampu)) {
            $daftarKelas = collect($user->kelas_diampu)->sort()->values();
        } else {
            $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        }

        $selectedKelas = $request->get('kelas', $daftarKelas->first() ?? '');
        if ($selectedKelas && ! $daftarKelas->contains($selectedKelas)) {
            $selectedKelas = $daftarKelas->first() ?? '';
        }
        $selectedMapelId = $request->get('mata_pelajaran_id', $mapels->first()->id ?? null);

        $selectedMapel = $mapels->where('id', $selectedMapelId)->first();
        $siswas = collect();
        $kegiatans = collect();
        $rekapNilai = [];
        $analytics = [
            'total_siswa' => 0,
            'tuntas_count' => 0,
            'belum_tuntas_count' => 0,
            'pct_tuntas' => 0,
            'highest' => 0,
            'lowest' => 0,
            'avg_kelas' => 0,
        ];

        if ($selectedKelas && $selectedMapelId && $selectedMapel) {
            $siswas = Siswa::where('kelas', $selectedKelas)->orderBy('nama')->get();
            $kegiatans = Kegiatan::where('mata_pelajaran_id', $selectedMapelId)
                ->where('kelas', $selectedKelas)
                ->orderBy('tanggal')
                ->get();

            $kkm = $selectedMapel->kkm ?? 75;
            $bTugas = $selectedMapel->bobot_tugas ?? 20;
            $bUh = $selectedMapel->bobot_uh ?? 30;
            $bUts = $selectedMapel->bobot_uts ?? 25;
            $bUas = $selectedMapel->bobot_uas ?? 25;

            $allFinalScores = [];
            $tuntasCount = 0;
            $belumTuntasCount = 0;

            foreach ($siswas as $siswa) {
                $scores = [];
                $scoresByJenis = ['Tugas' => [], 'UH' => [], 'UTS' => [], 'UAS' => []];

                foreach ($kegiatans as $kegiatan) {
                    $nilaiObj = Nilai::where('kegiatan_id', $kegiatan->id)
                        ->where('siswa_id', $siswa->id)
                        ->first();
                    $val = $nilaiObj ? $nilaiObj->nilai : null;
                    $scores[$kegiatan->id] = $val;

                    if ($val !== null && isset($scoresByJenis[$kegiatan->jenis])) {
                        $scoresByJenis[$kegiatan->jenis][] = $val;
                    }
                }

                $validScores = array_filter($scores, fn ($n) => $n !== null);
                $rataRataMurni = count($validScores) > 0 ? round(array_sum($validScores) / count($validScores), 1) : null;

                // Hitung Rata-Rata per Kategori untuk Bobot Penilaian
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

                $nilaiAkhirBerbobot = $weightTotal > 0 ? round($weightedSum / $weightTotal, 1) : $rataRataMurni;

                $predikat = '-';
                $statusKetuntasan = '-';
                $scoreUsed = $nilaiAkhirBerbobot ?? $rataRataMurni;

                if ($scoreUsed !== null) {
                    $allFinalScores[] = $scoreUsed;
                    if ($scoreUsed >= 90) {
                        $predikat = 'A (Sangat Baik)';
                    } elseif ($scoreUsed >= 80) {
                        $predikat = 'B (Baik)';
                    } elseif ($scoreUsed >= $kkm) {
                        $predikat = 'C (Cukup / Tuntas)';
                    } else {
                        $predikat = 'D (Perlu Bimbingan)';
                    }

                    if ($scoreUsed >= $kkm) {
                        $statusKetuntasan = 'TUNTAS';
                        $tuntasCount++;
                    } else {
                        $statusKetuntasan = 'BELUM TUNTAS';
                        $belumTuntasCount++;
                    }
                }

                $rekapNilai[$siswa->id] = [
                    'scores' => $scores,
                    'rata_rata' => $rataRataMurni,
                    'nilai_akhir' => $nilaiAkhirBerbobot,
                    'predikat' => $predikat,
                    'status_ketuntasan' => $statusKetuntasan,
                ];
            }

            $totalStudents = count($siswas);
            $totalEvaluated = count($allFinalScores);
            $analytics = [
                'total_siswa' => $totalStudents,
                'tuntas_count' => $tuntasCount,
                'belum_tuntas_count' => $belumTuntasCount,
                'pct_tuntas' => $totalEvaluated > 0 ? round(($tuntasCount / $totalEvaluated) * 100, 1) : 0,
                'highest' => count($allFinalScores) > 0 ? max($allFinalScores) : 0,
                'lowest' => count($allFinalScores) > 0 ? min($allFinalScores) : 0,
                'avg_kelas' => count($allFinalScores) > 0 ? round(array_sum($allFinalScores) / count($allFinalScores), 1) : 0,
            ];
        }

        return view('nilai.index', compact(
            'daftarKelas',
            'mapels',
            'selectedKelas',
            'selectedMapelId',
            'selectedMapel',
            'siswas',
            'kegiatans',
            'rekapNilai',
            'analytics',
            'isGuru'
        ));
    }

    public function createKegiatan(Request $request)
    {
        $user = auth()->user();
        $isGuru = $user && $user->role === 'guru';

        if ($isGuru && $user->mata_pelajaran_id) {
            $mapels = MataPelajaran::where('id', $user->mata_pelajaran_id)->get();
        } else {
            $mapels = MataPelajaran::orderBy('nama_mapel')->get();
        }

        if ($isGuru && ! empty($user->kelas_diampu) && is_array($user->kelas_diampu)) {
            $daftarKelas = collect($user->kelas_diampu)->sort()->values();
        } else {
            $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        }

        $selectedKelas = $request->get('kelas', $daftarKelas->first() ?? '');
        if ($selectedKelas && ! $daftarKelas->contains($selectedKelas)) {
            $selectedKelas = $daftarKelas->first() ?? '';
        }
        $selectedMapelId = $request->get('mata_pelajaran_id', $mapels->first()->id ?? null);

        $siswas = collect();
        if ($selectedKelas) {
            $siswas = Siswa::where('kelas', $selectedKelas)->orderBy('nama')->get();
        }

        return view('nilai.create_kegiatan', compact('daftarKelas', 'mapels', 'selectedKelas', 'selectedMapelId', 'siswas', 'isGuru'));
    }

    public function storeKegiatan(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'kelas' => 'required|string',
            'nama_kegiatan' => 'required|string|max:150',
            'jenis' => 'required|in:Tugas,UH,UTS,UAS',
            'tanggal' => 'required|date',
            'nilai' => 'array',
        ]);

        $kegiatan = Kegiatan::create([
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'kelas' => $request->kelas,
            'nama_kegiatan' => $request->nama_kegiatan,
            'jenis' => $request->jenis,
            'tanggal' => $request->tanggal,
        ]);

        if ($request->has('nilai')) {
            foreach ($request->nilai as $siswa_id => $val) {
                if ($val !== null && $val !== '') {
                    Nilai::create([
                        'kegiatan_id' => $kegiatan->id,
                        'siswa_id' => $siswa_id,
                        'nilai' => floatval($val),
                    ]);
                }
            }
        }

        return redirect()->route('nilai.index', [
            'kelas' => $request->kelas,
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
        ])->with('success', 'Kegiatan Penilaian & Nilai Siswa berhasil ditambahkan!');
    }

    public function editKegiatan($id)
    {
        $kegiatan = Kegiatan::with(['mataPelajaran', 'nilais'])->findOrFail($id);
        $siswas = Siswa::where('kelas', $kegiatan->kelas)->orderBy('nama')->get();
        $nilaiMap = $kegiatan->nilais->pluck('nilai', 'siswa_id')->toArray();

        return view('nilai.edit_kegiatan', compact('kegiatan', 'siswas', 'nilaiMap'));
    }

    public function updateKegiatan(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $request->validate([
            'nama_kegiatan' => 'required|string|max:150',
            'jenis' => 'required|in:Tugas,UH,UTS,UAS',
            'tanggal' => 'required|date',
            'nilai' => 'array',
        ]);

        $kegiatan->update([
            'nama_kegiatan' => $request->nama_kegiatan,
            'jenis' => $request->jenis,
            'tanggal' => $request->tanggal,
        ]);

        if ($request->has('nilai')) {
            foreach ($request->nilai as $siswa_id => $val) {
                if ($val !== null && $val !== '') {
                    Nilai::updateOrCreate(
                        ['kegiatan_id' => $kegiatan->id, 'siswa_id' => $siswa_id],
                        ['nilai' => floatval($val)]
                    );
                } else {
                    Nilai::where('kegiatan_id', $kegiatan->id)->where('siswa_id', $siswa_id)->delete();
                }
            }
        }

        return redirect()->route('nilai.index', [
            'kelas' => $kegiatan->kelas,
            'mata_pelajaran_id' => $kegiatan->mata_pelajaran_id,
        ])->with('success', 'Nilai kegiatan berhasil diperbarui!');
    }

    public function destroyKegiatan($id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kelas = $kegiatan->kelas;
        $mapelId = $kegiatan->mata_pelajaran_id;
        $kegiatan->delete();

        return redirect()->route('nilai.index', [
            'kelas' => $kelas,
            'mata_pelajaran_id' => $mapelId,
        ])->with('success', 'Kegiatan Penilaian berhasil dihapus!');
    }

    public function rekapPdf(Request $request)
    {
        $kelas = $request->get('kelas');
        $mapelId = $request->get('mata_pelajaran_id');

        $mapel = MataPelajaran::findOrFail($mapelId);
        $siswas = Siswa::where('kelas', $kelas)->orderBy('nama')->get();
        $kegiatans = Kegiatan::where('mata_pelajaran_id', $mapelId)
            ->where('kelas', $kelas)
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
            $rekap[$siswa->id] = [
                'scores' => $scores,
                'rata_rata' => $avg,
            ];
        }

        $pdf = Pdf::loadView('nilai.pdf_rekap', compact('kelas', 'mapel', 'siswas', 'kegiatans', 'rekap'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Rekap_Nilai_'.$mapel->kode_mapel.'_'.$kelas.'.pdf');
    }

    public function rekapExcel(Request $request)
    {
        $kelas = $request->get('kelas');
        $mapelId = $request->get('mata_pelajaran_id');

        $mapel = MataPelajaran::findOrFail($mapelId);
        $fileName = 'Rekap_Nilai_'.$mapel->kode_mapel.'_Kelas_'.$kelas.'.xlsx';

        return Excel::download(new NilaiExport($kelas, $mapelId), $fileName);
    }

    public function lembarKosongPdf(Request $request)
    {
        $kelas = $request->get('kelas');
        $mapelId = $request->get('mata_pelajaran_id');

        $mapel = MataPelajaran::find($mapelId);
        $siswas = Siswa::where('kelas', $kelas)->orderBy('nama')->get();

        $pdf = Pdf::loadView('nilai.pdf_lembar_kosong', compact('kelas', 'mapel', 'siswas'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Lembar_Cetak_Kosong_'.$kelas.'.pdf');
    }

    public function raporSiswaPdf($siswaId)
    {
        $siswa = Siswa::findOrFail($siswaId);
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();

        // Rekap Absensi Siswa
        $absensiCounts = \App\Models\Absensi::where('siswa_id', $siswa->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $rekapAbsensi = [
            'hadir' => $absensiCounts['Hadir'] ?? 0,
            'sakit' => $absensiCounts['Sakit'] ?? 0,
            'izin' => $absensiCounts['Izin'] ?? 0,
            'alpha' => $absensiCounts['Alpha'] ?? 0,
        ];

        // Rekap Nilai Siswa Semua Mapel
        $raporMapel = [];
        foreach ($mapels as $mapel) {
            $kegiatans = Kegiatan::where('mata_pelajaran_id', $mapel->id)
                ->where('kelas', $siswa->kelas)
                ->pluck('id');

            if ($kegiatans->isEmpty()) {
                $raporMapel[] = [
                    'mapel' => $mapel,
                    'nilai_akhir' => null,
                    'predikat' => '-',
                    'status' => 'Belum Ada Nilai',
                    'deskripsi' => 'Belum ada penilaian kegiatan untuk mata pelajaran ini.',
                ];
                continue;
            }

            $nilais = Nilai::whereIn('kegiatan_id', $kegiatans)
                ->where('siswa_id', $siswa->id)
                ->pluck('nilai');

            $avg = $nilais->count() > 0 ? round($nilais->avg(), 1) : null;
            $kkm = $mapel->kkm ?? 75;

            $predikat = '-';
            $status = '-';
            $deskripsi = 'Belum Mengikuti Penilaian';

            if ($avg !== null) {
                if ($avg >= 90) {
                    $predikat = 'A';
                    $status = 'Sangat Baik';
                    $deskripsi = 'Menunjukkan penguasaan kompetensi yang sangat baik dalam seluruh materi.';
                } elseif ($avg >= 80) {
                    $predikat = 'B';
                    $status = 'Baik';
                    $deskripsi = 'Menunjukkan penguasaan kompetensi yang baik dalam materi pembelajaran.';
                } elseif ($avg >= $kkm) {
                    $predikat = 'C';
                    $status = 'Cukup';
                    $deskripsi = 'Telah mencapai kriteria ketuntasan minimal (KKM) yang ditetapkan.';
                } else {
                    $predikat = 'D';
                    $status = 'Perlu Bimbingan';
                    $deskripsi = 'Perlu bimbingan dan remedial untuk mencapai kriteria ketuntasan minimal (KKM).';
                }
            }

            $raporMapel[] = [
                'mapel' => $mapel,
                'nilai_akhir' => $avg,
                'predikat' => $predikat,
                'status' => $status,
                'deskripsi' => $deskripsi,
            ];
        }

        $pdf = Pdf::loadView('nilai.pdf_rapor_siswa', compact('siswa', 'raporMapel', 'rekapAbsensi'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Rapor_Siswa_'.$siswa->nis.'_'.str_replace(' ', '_', $siswa->nama).'.pdf');
    }
}

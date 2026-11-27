<?php

namespace App\Http\Controllers;

use App\Exports\NilaiExport;
use App\Exports\NilaiTemplateExport;
use App\Imports\NilaiImport;
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
        $isGuru = $user && in_array($user->role, ['guru', 'walikelas']);

        // Filter Mapel & Kelas jika Role Guru
        if ($isGuru) {
            $assignedIds = $user->assigned_mapel_ids;
            $mapels = !empty($assignedIds)
                ? MataPelajaran::whereIn('id', $assignedIds)->orderBy('tingkat')->orderBy('nama_mapel')->get()
                : collect();
        } else {
            $mapels = MataPelajaran::orderBy('tingkat')->orderBy('nama_mapel')->get();
        }

        $selectedMapelId = $request->get('mata_pelajaran_id', $mapels->first()->id ?? null);
        $selectedMapel = $mapels->where('id', $selectedMapelId)->first();

        // Tentukan daftar kelas guru / semua
        if ($isGuru && ! empty($user->kelas_diampu) && is_array($user->kelas_diampu)) {
            $rawKelas = collect($user->kelas_diampu);
        } else {
            $rawKelas = Siswa::select('kelas')->distinct()->pluck('kelas');
        }

        // Urutkan kelas secara logis (VII, VIII, IX)
        $order = ['VII' => 1, 'VIII' => 2, 'IX' => 3];
        $sortedKelas = $rawKelas->sort(function ($a, $b) use ($order) {
            $tA = $order[strtoupper(MataPelajaran::getTingkatFromKelas($a))] ?? 99;
            $tB = $order[strtoupper(MataPelajaran::getTingkatFromKelas($b))] ?? 99;
            if ($tA !== $tB) return $tA <=> $tB;
            return strnatcasecmp($a, $b);
        })->values();

        // Filter kelas sesuai tingkat mapel yang dipilih
        if ($selectedMapel) {
            $daftarKelas = $selectedMapel->filterKelasCollection($sortedKelas);
        } else {
            $daftarKelas = $sortedKelas;
        }

        $selectedKelas = $request->get('kelas');
        if (! $selectedKelas || ! $daftarKelas->contains($selectedKelas)) {
            $selectedKelas = $daftarKelas->first() ?? '';
        }
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

        $selectedSemester = (int) $request->get('semester', Kegiatan::currentSemester());
        $selectedTahunAjaran = $request->get('tahun_ajaran', Kegiatan::currentTahunAjaran());

        $daftarSemester = [
            1 => 'Semester 1 (Ganjil)',
            2 => 'Semester 2 (Genap)',
        ];
        $daftarTahunAjaran = ['2026/2027'];

        if ($selectedKelas && $selectedMapelId && $selectedMapel) {
            $siswas = Siswa::where('kelas', $selectedKelas)->orderBy('nama')->get();
            $kegiatans = Kegiatan::where('mata_pelajaran_id', $selectedMapelId)
                ->where('kelas', $selectedKelas)
                ->where('semester', $selectedSemester)
                ->where('tahun_ajaran', $selectedTahunAjaran)
                ->orderBy('tanggal')
                ->get();

            [$rekapNilai, $analytics] = self::calculateRekapData($siswas, $kegiatans, $selectedMapel);
        }

        return view('nilai.index', compact(
            'daftarKelas',
            'mapels',
            'selectedKelas',
            'selectedMapelId',
            'selectedMapel',
            'selectedSemester',
            'selectedTahunAjaran',
            'daftarSemester',
            'daftarTahunAjaran',
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
        $isGuru = $user && in_array($user->role, ['guru', 'walikelas']);

        if ($isGuru) {
            $assignedIds = $user->assigned_mapel_ids;
            $mapels = !empty($assignedIds)
                ? MataPelajaran::whereIn('id', $assignedIds)->orderBy('tingkat')->orderBy('nama_mapel')->get()
                : collect();
        } else {
            $mapels = MataPelajaran::orderBy('tingkat')->orderBy('nama_mapel')->get();
        }

        $selectedMapelId = $request->get('mata_pelajaran_id', $mapels->first()->id ?? null);
        $selectedMapel = $mapels->where('id', $selectedMapelId)->first();

        if ($isGuru && ! empty($user->kelas_diampu) && is_array($user->kelas_diampu)) {
            $rawKelas = collect($user->kelas_diampu);
        } else {
            $rawKelas = Siswa::select('kelas')->distinct()->pluck('kelas');
        }

        $order = ['VII' => 1, 'VIII' => 2, 'IX' => 3];
        $sortedKelas = $rawKelas->sort(function ($a, $b) use ($order) {
            $tA = $order[strtoupper(MataPelajaran::getTingkatFromKelas($a))] ?? 99;
            $tB = $order[strtoupper(MataPelajaran::getTingkatFromKelas($b))] ?? 99;
            if ($tA !== $tB) return $tA <=> $tB;
            return strnatcasecmp($a, $b);
        })->values();

        $allDaftarKelas = $sortedKelas;

        if ($selectedMapel) {
            $daftarKelas = $selectedMapel->filterKelasCollection($sortedKelas);
        } else {
            $daftarKelas = $sortedKelas;
        }

        $selectedKelas = $request->get('kelas');
        if (! $selectedKelas || ! $daftarKelas->contains($selectedKelas)) {
            $selectedKelas = $daftarKelas->first() ?? '';
        }

        $siswasByKelas = Siswa::whereIn('kelas', $allDaftarKelas)
            ->orderBy('kelas')
            ->orderBy('nama')
            ->get()
            ->groupBy('kelas');

        $siswas = $siswasByKelas->get($selectedKelas, collect());

        $selectedSemester = (int) $request->get('semester', Kegiatan::currentSemester());
        $selectedTahunAjaran = $request->get('tahun_ajaran', Kegiatan::currentTahunAjaran());
        $daftarSemester = [
            1 => 'Semester 1 (Ganjil)',
            2 => 'Semester 2 (Genap)',
        ];
        $daftarTahunAjaran = ['2026/2027'];

        return view('nilai.create_kegiatan', compact(
            'daftarKelas',
            'allDaftarKelas',
            'mapels',
            'selectedKelas',
            'selectedMapelId',
            'selectedSemester',
            'selectedTahunAjaran',
            'daftarSemester',
            'daftarTahunAjaran',
            'siswas',
            'siswasByKelas',
            'isGuru'
        ));
    }

    public function storeKegiatan(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'kelas' => 'required|string',
            'nama_kegiatan' => 'required|string|max:150',
            'jenis' => 'required|in:Tugas,UH,UTS,UAS',
            'tanggal' => 'required|date',
            'nilai' => 'nullable|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ], [
            'nilai.*.max' => 'Nilai siswa tidak boleh lebih dari 100.',
            'nilai.*.min' => 'Nilai siswa tidak boleh kurang dari 0.',
        ]);

        $semester = $request->filled('semester') ? (int) $request->semester : Kegiatan::determineSemester($request->tanggal);
        $tahunAjaran = $request->filled('tahun_ajaran') ? $request->tahun_ajaran : Kegiatan::determineTahunAjaran($request->tanggal);

        $kegiatan = Kegiatan::create([
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'kelas' => $request->kelas,
            'nama_kegiatan' => $request->nama_kegiatan,
            'jenis' => $request->jenis,
            'semester' => $semester,
            'tahun_ajaran' => $tahunAjaran,
            'tanggal' => $request->tanggal,
        ]);

        if ($request->hasFile('file_excel')) {
            try {
                $import = new NilaiImport($kegiatan);
                Excel::import($import, $request->file('file_excel'));
            } catch (\Throwable $e) {}
        }

        if ($request->has('nilai')) {
            $mapel = MataPelajaran::find($request->mata_pelajaran_id);
            $mapelName = $mapel ? $mapel->nama_mapel : 'Mata Pelajaran';
            $kkm = $mapel->kkm ?? 75;
            $currentUser = \Illuminate\Support\Facades\Auth::user();
            $namaGuru = $currentUser ? ($currentUser->name ?: $currentUser->username) : 'Guru Pengampu';

            foreach ($request->nilai as $siswa_id => $val) {
                if ($val !== null && $val !== '') {
                    $score = min(100, max(0, floatval($val)));
                    Nilai::create([
                        'kegiatan_id' => $kegiatan->id,
                        'siswa_id' => $siswa_id,
                        'nilai' => $score,
                    ]);

                    try {
                        $statusKetuntasan = $score >= $kkm ? 'Tuntas' : 'Perlu Remedial';
                        $siswa = Siswa::find($siswa_id);
                        if ($siswa) {
                            \App\Services\NotificationService::sendToSiswa(
                                $siswa->id,
                                "🔔 Nilai Baru: {$mapelName}",
                                "Nilai {$kegiatan->nama_kegiatan} Ananda {$siswa->nama} telah diinput oleh {$namaGuru}: {$score} ({$statusKetuntasan}). KKM: {$kkm}.",
                                [
                                    'type' => 'nilai',
                                    'kegiatan_id' => $kegiatan->id,
                                    'siswa_id' => $siswa->id,
                                    'mapel' => $mapelName,
                                    'nilai' => $score,
                                    'guru' => $namaGuru,
                                ]
                            );
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        return redirect()->route('nilai.index', [
            'kelas' => $request->kelas,
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'semester' => $semester,
            'tahun_ajaran' => $tahunAjaran,
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
            'nilai' => 'nullable|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ], [
            'nilai.*.max' => 'Nilai siswa tidak boleh lebih dari 100.',
            'nilai.*.min' => 'Nilai siswa tidak boleh kurang dari 0.',
        ]);

        $semester = $request->filled('semester') ? (int) $request->semester : Kegiatan::determineSemester($request->tanggal);
        $tahunAjaran = $request->filled('tahun_ajaran') ? $request->tahun_ajaran : Kegiatan::determineTahunAjaran($request->tanggal);

        $kegiatan->update([
            'nama_kegiatan' => $request->nama_kegiatan,
            'jenis' => $request->jenis,
            'semester' => $semester,
            'tahun_ajaran' => $tahunAjaran,
            'tanggal' => $request->tanggal,
        ]);

        if ($request->hasFile('file_excel')) {
            try {
                $import = new NilaiImport($kegiatan);
                Excel::import($import, $request->file('file_excel'));
            } catch (\Throwable $e) {}
        }

        if ($request->has('nilai')) {
            $mapel = $kegiatan->mataPelajaran;
            $mapelName = $mapel ? $mapel->nama_mapel : 'Mata Pelajaran';
            $kkm = $mapel->kkm ?? 75;
            $currentUser = \Illuminate\Support\Facades\Auth::user();
            $namaGuru = $currentUser ? ($currentUser->name ?: $currentUser->username) : 'Guru Pengampu';

            foreach ($request->nilai as $siswa_id => $val) {
                if ($val !== null && $val !== '') {
                    $score = min(100, max(0, floatval($val)));
                    Nilai::updateOrCreate(
                        ['kegiatan_id' => $kegiatan->id, 'siswa_id' => $siswa_id],
                        ['nilai' => $score]
                    );

                    try {
                        $statusKetuntasan = $score >= $kkm ? 'Tuntas' : 'Perlu Remedial';
                        $siswa = Siswa::find($siswa_id);
                        if ($siswa) {
                            \App\Services\NotificationService::sendToSiswa(
                                $siswa->id,
                                "🔔 Nilai Diperbarui: {$mapelName}",
                                "Nilai {$kegiatan->nama_kegiatan} Ananda {$siswa->nama} telah diperbarui oleh {$namaGuru}: {$score} ({$statusKetuntasan}). KKM: {$kkm}.",
                                [
                                    'type' => 'nilai',
                                    'kegiatan_id' => $kegiatan->id,
                                    'siswa_id' => $siswa->id,
                                    'mapel' => $mapelName,
                                    'nilai' => $score,
                                    'guru' => $namaGuru,
                                ]
                            );
                        }
                    } catch (\Throwable $e) {}
                } else {
                    Nilai::where('kegiatan_id', $kegiatan->id)->where('siswa_id', $siswa_id)->delete();
                }
            }
        }

        return redirect()->route('nilai.index', [
            'kelas' => $kegiatan->kelas,
            'mata_pelajaran_id' => $kegiatan->mata_pelajaran_id,
            'semester' => $kegiatan->semester,
            'tahun_ajaran' => $kegiatan->tahun_ajaran,
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

    /**
     * Hitung rekap nilai (rata-rata murni, nilai akhir berbobot, predikat, dan ketuntasan)
     */
    public static function calculateRekapData($siswas, $kegiatans, $mapel): array
    {
        $kkm = $mapel->kkm ?? 75;
        $bTugas = $mapel->bobot_tugas ?? 20;
        $bUh = $mapel->bobot_uh ?? 30;
        $bUts = $mapel->bobot_uts ?? 25;
        $bUas = $mapel->bobot_uas ?? 25;

        $totalKegiatan = $kegiatans->count();
        $classKegiatanCounts = [
            'Tugas' => $kegiatans->where('jenis', 'Tugas')->count(),
            'UH'    => $kegiatans->where('jenis', 'UH')->count(),
            'UTS'   => $kegiatans->where('jenis', 'UTS')->count(),
            'UAS'   => $kegiatans->where('jenis', 'UAS')->count(),
        ];
        $activeClassWeights = [
            'Tugas' => $classKegiatanCounts['Tugas'] > 0 ? $bTugas : 0,
            'UH'    => $classKegiatanCounts['UH'] > 0 ? $bUh : 0,
            'UTS'   => $classKegiatanCounts['UTS'] > 0 ? $bUts : 0,
            'UAS'   => $classKegiatanCounts['UAS'] > 0 ? $bUas : 0,
        ];
        $totalActiveWeight = array_sum($activeClassWeights);

        $allFinalScores = [];
        $tuntasCount = 0;
        $belumTuntasCount = 0;
        $rekapNilai = [];

        // Preload nilais agar tidak terjadi N+1 query
        $kegiatanIds = $kegiatans->pluck('id');
        $siswaIds = $siswas->pluck('id');
        $allNilais = Nilai::whereIn('kegiatan_id', $kegiatanIds)
            ->whereIn('siswa_id', $siswaIds)
            ->get()
            ->groupBy('siswa_id');

        foreach ($siswas as $siswa) {
            $scores = [];
            $scoresByJenis = ['Tugas' => [], 'UH' => [], 'UTS' => [], 'UAS' => []];
            $siswaNilais = $allNilais->get($siswa->id, collect())->keyBy('kegiatan_id');

            foreach ($kegiatans as $kegiatan) {
                $nilaiObj = $siswaNilais->get($kegiatan->id);
                $val = $nilaiObj ? $nilaiObj->nilai : null;
                $scores[$kegiatan->id] = $val;

                if ($val !== null && isset($scoresByJenis[$kegiatan->jenis])) {
                    $scoresByJenis[$kegiatan->jenis][] = $val;
                }
            }

            $validScores = array_filter($scores, fn ($n) => $n !== null);
            $hasAnyScore = count($validScores) > 0;

            if ($hasAnyScore) {
                // Rata-rata murni terhadap total kegiatan di kelas
                $rataRataMurni = $totalKegiatan > 0 ? round(array_sum($validScores) / $totalKegiatan, 1) : null;

                // Hitung Rata-Rata per Kategori berdasarkan jumlah kegiatan kategori tersebut di kelas
                $avgTugas = $classKegiatanCounts['Tugas'] > 0 ? array_sum($scoresByJenis['Tugas']) / $classKegiatanCounts['Tugas'] : 0;
                $avgUh    = $classKegiatanCounts['UH'] > 0 ? array_sum($scoresByJenis['UH']) / $classKegiatanCounts['UH'] : 0;
                $avgUts   = $classKegiatanCounts['UTS'] > 0 ? array_sum($scoresByJenis['UTS']) / $classKegiatanCounts['UTS'] : 0;
                $avgUas   = $classKegiatanCounts['UAS'] > 0 ? array_sum($scoresByJenis['UAS']) / $classKegiatanCounts['UAS'] : 0;

                $weightedSum = ($avgTugas * $activeClassWeights['Tugas'])
                             + ($avgUh * $activeClassWeights['UH'])
                             + ($avgUts * $activeClassWeights['UTS'])
                             + ($avgUas * $activeClassWeights['UAS']);

                $nilaiAkhirBerbobot = $totalActiveWeight > 0 ? round($weightedSum / $totalActiveWeight, 1) : $rataRataMurni;
            } else {
                $rataRataMurni = null;
                $nilaiAkhirBerbobot = null;
            }

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

        return [$rekapNilai, $analytics];
    }

    public function rekapPdf(Request $request)
    {
        $kelas = $request->get('kelas');
        $mapelId = $request->get('mata_pelajaran_id');
        $semester = (int) $request->get('semester', Kegiatan::currentSemester());
        $tahunAjaran = $request->get('tahun_ajaran', Kegiatan::currentTahunAjaran());

        $mapel = MataPelajaran::findOrFail($mapelId);
        $siswas = Siswa::where('kelas', $kelas)->orderBy('nama')->get();
        $kegiatans = Kegiatan::where('mata_pelajaran_id', $mapelId)
            ->where('kelas', $kelas)
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->orderBy('tanggal')
            ->get();

        [$rekap, $analytics] = self::calculateRekapData($siswas, $kegiatans, $mapel);

        $pdf = Pdf::loadView('nilai.pdf_rekap', compact('kelas', 'mapel', 'siswas', 'kegiatans', 'rekap', 'analytics', 'semester', 'tahunAjaran'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Rekap_Nilai_'.$mapel->kode_mapel.'_'.$kelas.'_Sem_'.$semester.'.pdf');
    }

    public function rekapExcel(Request $request)
    {
        $kelas = $request->get('kelas');
        $mapelId = $request->get('mata_pelajaran_id');
        $semester = (int) $request->get('semester', Kegiatan::currentSemester());
        $tahunAjaran = $request->get('tahun_ajaran', Kegiatan::currentTahunAjaran());

        $mapel = MataPelajaran::findOrFail($mapelId);
        $fileName = 'Rekap_Nilai_'.$mapel->kode_mapel.'_Kelas_'.$kelas.'_Sem_'.$semester.'.xlsx';

        return Excel::download(new NilaiExport($kelas, $mapelId, $semester, $tahunAjaran), $fileName);
    }

    public function downloadTemplateExcel(Request $request)
    {
        $kelas = $request->get('kelas');
        $mapelId = $request->get('mata_pelajaran_id');
        $kegiatanId = $request->get('kegiatan_id');

        if (empty($kelas)) {
            return back()->with('error', 'Kelas wajib dipilih untuk mengunduh template nilai.');
        }

        $mapel = $mapelId ? MataPelajaran::find($mapelId) : null;
        $mapelCode = $mapel ? preg_replace('/[^a-zA-Z0-9]/', '_', $mapel->kode_mapel ?: $mapel->nama_mapel) : 'Mapel';
        $cleanKelas = preg_replace('/[^a-zA-Z0-9]/', '_', $kelas);

        $kegiatan = $kegiatanId ? Kegiatan::find($kegiatanId) : null;
        $kegiatanPart = $kegiatan ? '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $kegiatan->nama_kegiatan) : '';

        $fileName = 'Template_Nilai_' . $cleanKelas . '_' . $mapelCode . $kegiatanPart . '.xlsx';

        return Excel::download(new NilaiTemplateExport($kelas, $kegiatanId), $fileName);
    }

    public function parseExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'kelas' => 'required|string',
        ], [
            'file_excel.required' => 'Pilih file Excel yang ingin dimuat.',
            'file_excel.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV.',
            'kelas.required' => 'Kelas harus dipilih.',
        ]);

        try {
            $kelas = $request->kelas;
            $file = $request->file('file_excel');

            $importHelper = new class implements \Maatwebsite\Excel\Concerns\ToArray, \Maatwebsite\Excel\Concerns\WithHeadingRow, \Maatwebsite\Excel\Concerns\WithCustomCsvSettings {
                public function array(array $array) {
                    return $array;
                }
                public function getCsvSettings(): array {
                    return [
                        'delimiter' => ';',
                        'enclosure' => '"',
                        'input_encoding' => 'UTF-8',
                    ];
                }
            };

            $sheets = Excel::toArray($importHelper, $file);
            $rows = $sheets[0] ?? [];

            $siswas = Siswa::where('kelas', $kelas)->get();
            $siswaByNis = [];
            $siswaByCleanNis = [];
            $siswaByName = [];

            foreach ($siswas as $s) {
                $rawNis = trim((string) $s->nis);
                $cleanNis = ltrim($rawNis, '0');
                $cleanName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string) $s->nama)));

                $siswaByNis[$rawNis] = $s;
                if ($cleanNis !== '') {
                    $siswaByCleanNis[$cleanNis] = $s;
                }
                if ($cleanName !== '') {
                    $siswaByName[$cleanName] = $s;
                }
            }

            $matched = [];
            $unmatched = [];
            $skipped = [];

            foreach ($rows as $index => $row) {
                $cleanRow = [];
                foreach ($row as $key => $val) {
                    $k = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string) $key)));
                    $cleanRow[$k] = is_string($val) ? trim($val) : $val;
                }

                // 1. NISN
                $rawNisInput = $cleanRow['nisn'] 
                    ?? $cleanRow['nis'] 
                    ?? $cleanRow['noinduk'] 
                    ?? $cleanRow['nomorinduk'] 
                    ?? $cleanRow['nissiswa']
                    ?? (isset($row[1]) ? trim((string) $row[1]) : null);
                $rawNisStr = is_string($rawNisInput) ? trim(ltrim($rawNisInput, "'")) : (string) $rawNisInput;

                // 2. Nama
                $namaInput = $cleanRow['namasiswa'] 
                    ?? $cleanRow['nama'] 
                    ?? $cleanRow['namalengkap'] 
                    ?? $cleanRow['siswa'] 
                    ?? (isset($row[2]) ? trim((string) $row[2]) : '');

                // 3. Nilai
                $nilaiInput = $cleanRow['nilai'] 
                    ?? $cleanRow['nilai0100'] 
                    ?? $cleanRow['skor'] 
                    ?? $cleanRow['score'] 
                    ?? $cleanRow['angka']
                    ?? (isset($row[3]) ? $row[3] : null);

                if (empty($rawNisStr) && empty($namaInput) && ($nilaiInput === null || $nilaiInput === '')) {
                    continue;
                }

                $namaUpper = strtoupper(trim((string) $namaInput));
                if ($namaUpper === 'NAMA' || $namaUpper === 'NAMA SISWA' || $namaUpper === 'NAMA_SISWA') {
                    continue;
                }

                $matchedSiswa = null;
                if (!empty($rawNisStr)) {
                    if (isset($siswaByNis[$rawNisStr])) {
                        $matchedSiswa = $siswaByNis[$rawNisStr];
                    } else {
                        $cleanNisSearch = ltrim($rawNisStr, '0');
                        if (isset($siswaByCleanNis[$cleanNisSearch])) {
                            $matchedSiswa = $siswaByCleanNis[$cleanNisSearch];
                        } else {
                            $paddedNis = str_pad($rawNisStr, 10, '0', STR_PAD_LEFT);
                            if (isset($siswaByNis[$paddedNis])) {
                                $matchedSiswa = $siswaByNis[$paddedNis];
                            }
                        }
                    }
                }

                if (!$matchedSiswa && !empty($namaInput)) {
                    $cleanSearchName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string) $namaInput)));
                    if (isset($siswaByName[$cleanSearchName])) {
                        $matchedSiswa = $siswaByName[$cleanSearchName];
                    }
                }

                if (!$matchedSiswa) {
                    $unmatched[] = [
                        'baris' => $index + 2,
                        'nisn' => $rawNisStr ?: '-',
                        'nama' => $namaInput ?: '-',
                        'alasan' => "Siswa tidak ditemukan di kelas {$kelas}",
                    ];
                    continue;
                }

                if ($nilaiInput === null || $nilaiInput === '' || $nilaiInput === '-') {
                    $skipped[] = [
                        'siswa_id' => $matchedSiswa->id,
                        'nama' => $matchedSiswa->nama,
                        'alasan' => 'Nilai kosong',
                    ];
                    continue;
                }

                if (is_string($nilaiInput)) {
                    $nilaiInput = str_replace(',', '.', trim($nilaiInput));
                }

                if (!is_numeric($nilaiInput)) {
                    $skipped[] = [
                        'siswa_id' => $matchedSiswa->id,
                        'nama' => $matchedSiswa->nama,
                        'alasan' => "Format nilai tidak valid ('{$nilaiInput}')",
                    ];
                    continue;
                }

                $score = min(100, max(0, floatval($nilaiInput)));

                $matched[] = [
                    'siswa_id' => $matchedSiswa->id,
                    'nisn' => $matchedSiswa->nis,
                    'nama' => $matchedSiswa->nama,
                    'nilai' => $score,
                ];
            }

            return response()->json([
                'status' => 'success',
                'kelas' => $kelas,
                'total_rows' => count($rows),
                'matched_count' => count($matched),
                'skipped_count' => count($skipped),
                'unmatched_count' => count($unmatched),
                'matched' => $matched,
                'unmatched' => $unmatched,
                'skipped' => $skipped,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'mode' => 'nullable|in:existing,new',
        ], [
            'file_excel.required' => 'File Excel nilai wajib diunggah.',
            'file_excel.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV.',
            'file_excel.max' => 'Ukuran file Excel maksimal 5MB.',
        ]);

        $mode = $request->input('mode', 'new');
        $kegiatan = null;

        if ($mode === 'existing' || $request->filled('kegiatan_id')) {
            $request->validate([
                'kegiatan_id' => 'required|exists:kegiatans,id',
            ], [
                'kegiatan_id.required' => 'Pilih kegiatan penilaian yang ingin diimpor nilainya.',
            ]);
            $kegiatan = Kegiatan::findOrFail($request->kegiatan_id);
        } else {
            $request->validate([
                'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
                'kelas' => 'required|string',
                'nama_kegiatan' => 'required|string|max:150',
                'jenis' => 'required|in:Tugas,UH,UTS,UAS',
                'tanggal' => 'required|date',
            ], [
                'mata_pelajaran_id.required' => 'Mata pelajaran wajib dipilih.',
                'kelas.required' => 'Kelas wajib dipilih.',
                'nama_kegiatan.required' => 'Nama kegiatan penilaian wajib diisi.',
                'jenis.required' => 'Jenis penilaian wajib dipilih.',
                'tanggal.required' => 'Tanggal kegiatan penilaian wajib diisi.',
            ]);

            $semester = $request->filled('semester') ? (int) $request->semester : Kegiatan::determineSemester($request->tanggal);
            $tahunAjaran = $request->filled('tahun_ajaran') ? $request->tahun_ajaran : Kegiatan::determineTahunAjaran($request->tanggal);

            $kegiatan = Kegiatan::create([
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'kelas' => $request->kelas,
                'nama_kegiatan' => $request->nama_kegiatan,
                'jenis' => $request->jenis,
                'semester' => $semester,
                'tahun_ajaran' => $tahunAjaran,
                'tanggal' => $request->tanggal,
            ]);
        }

        try {
            $import = new NilaiImport($kegiatan);
            Excel::import($import, $request->file('file_excel'));

            $message = "Berhasil mengimpor {$import->successCount} nilai siswa untuk kegiatan '{$kegiatan->nama_kegiatan}' ({$import->tuntasCount} Tuntas, {$import->remedialCount} Perlu Remedial).";
            if ($import->skippedCount > 0) {
                $message .= " Sebanyak {$import->skippedCount} baris/siswa dilewati atau bernilai kosong.";
            }

            return redirect()->route('nilai.index', [
                'kelas' => $kegiatan->kelas,
                'mata_pelajaran_id' => $kegiatan->mata_pelajaran_id,
                'semester' => $kegiatan->semester,
                'tahun_ajaran' => $kegiatan->tahun_ajaran,
            ])->with('success', $message);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage())->withInput();
        }
    }

    public function lembarKosongPdf(Request $request)
    {
        $kelas = $request->get('kelas');
        $mapelId = $request->get('mata_pelajaran_id');
        $semester = (int) $request->get('semester', Kegiatan::currentSemester());
        $tahunAjaran = $request->get('tahun_ajaran', Kegiatan::currentTahunAjaran());

        $mapel = MataPelajaran::find($mapelId);
        $siswas = Siswa::where('kelas', $kelas)->orderBy('nama')->get();

        $pdf = Pdf::loadView('nilai.pdf_lembar_kosong', compact('kelas', 'mapel', 'siswas', 'semester', 'tahunAjaran'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Lembar_Cetak_Kosong_'.$kelas.'_Sem_'.$semester.'.pdf');
    }

    public function raporSiswaPdf($siswaId)
    {
        $siswa = Siswa::findOrFail($siswaId);
        $semester = (int) request('semester', Kegiatan::currentSemester());
        $tahunAjaran = request('tahun_ajaran', Kegiatan::currentTahunAjaran());
        $tingkat = MataPelajaran::getTingkatFromKelas($siswa->kelas);

        // Filter mata pelajaran sesuai tingkat siswa (7, 8, 9, atau Semua)
        $mapelQuery = MataPelajaran::query();
        if ($tingkat !== 'Semua') {
            $mapelQuery->where(function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat)
                  ->orWhere('tingkat', 'Semua')
                  ->orWhereNull('tingkat');
            });
        }
        $mapels = $mapelQuery->orderBy('nama_mapel')->get();

        // Rekap Absensi Siswa Sesuai Semester
        $absensiQuery = \App\Models\Absensi::where('siswa_id', $siswa->id);
        if ($semester == 1) {
            $absensiQuery->whereBetween('tanggal', ['2026-07-01', '2026-12-31']);
        } else {
            $absensiQuery->whereBetween('tanggal', ['2027-01-01', '2027-06-30']);
        }
        $absensiCounts = $absensiQuery
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $rekapAbsensi = [
            'hadir' => $absensiCounts['Hadir'] ?? 0,
            'sakit' => $absensiCounts['Sakit'] ?? 0,
            'izin' => $absensiCounts['Izin'] ?? 0,
            'alpha' => $absensiCounts['Alpha'] ?? 0,
        ];

        // Rekap Nilai Siswa Sesuai Mapel dan Semester
        $raporMapel = [];
        foreach ($mapels as $mapel) {
            $kegiatans = Kegiatan::where('mata_pelajaran_id', $mapel->id)
                ->where('kelas', $siswa->kelas)
                ->where('semester', $semester)
                ->where('tahun_ajaran', $tahunAjaran)
                ->get();

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

            $bTugas = $mapel->bobot_tugas ?? 20;
            $bUh = $mapel->bobot_uh ?? 30;
            $bUts = $mapel->bobot_uts ?? 25;
            $bUas = $mapel->bobot_uas ?? 25;

            $totalKegiatan = $kegiatans->count();
            $classKegiatanCounts = [
                'Tugas' => $kegiatans->where('jenis', 'Tugas')->count(),
                'UH'    => $kegiatans->where('jenis', 'UH')->count(),
                'UTS'   => $kegiatans->where('jenis', 'UTS')->count(),
                'UAS'   => $kegiatans->where('jenis', 'UAS')->count(),
            ];
            $activeClassWeights = [
                'Tugas' => $classKegiatanCounts['Tugas'] > 0 ? $bTugas : 0,
                'UH'    => $classKegiatanCounts['UH'] > 0 ? $bUh : 0,
                'UTS'   => $classKegiatanCounts['UTS'] > 0 ? $bUts : 0,
                'UAS'   => $classKegiatanCounts['UAS'] > 0 ? $bUas : 0,
            ];
            $totalActiveWeight = array_sum($activeClassWeights);

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
            $hasAnyScore = count($validScores) > 0;

            if ($hasAnyScore) {
                // Solusi A: Nilai kegiatan yang belum dikerjakan dihitung 0 terhadap total kegiatan di kelas
                $rataRataMurni = $totalKegiatan > 0 ? round(array_sum($validScores) / $totalKegiatan, 1) : null;

                // Hitung Rata-Rata per Kategori berdasarkan jumlah kegiatan kategori tersebut di kelas
                $avgTugas = $classKegiatanCounts['Tugas'] > 0 ? array_sum($scoresByJenis['Tugas']) / $classKegiatanCounts['Tugas'] : 0;
                $avgUh    = $classKegiatanCounts['UH'] > 0 ? array_sum($scoresByJenis['UH']) / $classKegiatanCounts['UH'] : 0;
                $avgUts   = $classKegiatanCounts['UTS'] > 0 ? array_sum($scoresByJenis['UTS']) / $classKegiatanCounts['UTS'] : 0;
                $avgUas   = $classKegiatanCounts['UAS'] > 0 ? array_sum($scoresByJenis['UAS']) / $classKegiatanCounts['UAS'] : 0;

                $weightedSum = ($avgTugas * $activeClassWeights['Tugas'])
                             + ($avgUh * $activeClassWeights['UH'])
                             + ($avgUts * $activeClassWeights['UTS'])
                             + ($avgUas * $activeClassWeights['UAS']);

                $nilaiAkhir = $totalActiveWeight > 0 ? round($weightedSum / $totalActiveWeight, 1) : $rataRataMurni;
            } else {
                $rataRataMurni = null;
                $nilaiAkhir = null;
            }
            $kkm = $mapel->kkm ?? 75;

            $predikat = '-';
            $status = '-';
            $deskripsi = 'Belum Mengikuti Penilaian';

            if ($nilaiAkhir !== null) {
                if ($nilaiAkhir >= 90) {
                    $predikat = 'A';
                    $status = 'Sangat Baik';
                    $deskripsi = 'Menunjukkan penguasaan kompetensi yang sangat baik dalam seluruh materi.';
                } elseif ($nilaiAkhir >= 80) {
                    $predikat = 'B';
                    $status = 'Baik';
                    $deskripsi = 'Menunjukkan penguasaan kompetensi yang baik dalam materi pembelajaran.';
                } elseif ($nilaiAkhir >= $kkm) {
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
                'nilai_akhir' => $nilaiAkhir,
                'predikat' => $predikat,
                'status' => $status,
                'deskripsi' => $deskripsi,
            ];
        }

        $waliKelas = \App\Models\WaliKelas::where('kelas', $siswa->kelas)->with('user')->first();
        $waliUser = $waliKelas ? $waliKelas->user : null;

        $pdf = Pdf::loadView('nilai.pdf_rapor_siswa', compact('siswa', 'raporMapel', 'rekapAbsensi', 'waliUser', 'semester', 'tahunAjaran'))
            ->setPaper('a4', 'portrait');

        $safeNis = $siswa->nis ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $siswa->nis) : $siswa->id;
        $safeNama = preg_replace('/[^A-Za-z0-9_\-]/', '_', $siswa->nama);

        return $pdf->download('Rapor_Siswa_'.$safeNis.'_'.$safeNama.'_Sem_'.$semester.'.pdf');
    }

    /**
     * Monitoring Keterisian Penilaian Mata Pelajaran (Khusus Akun Admin)
     */
    public function monitoring(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Fitur monitoring penilaian ini khusus untuk akun Administrator.');
        }

        $selectedSemester = (int) $request->get('semester', Kegiatan::currentSemester());
        $selectedTahunAjaran = $request->get('tahun_ajaran', Kegiatan::currentTahunAjaran());
        $selectedTingkat = $request->get('tingkat', 'Semua');
        $selectedStatus = $request->get('status', 'semua');
        $searchQuery = trim($request->get('q', ''));

        // Urutan logis kelas (VII, VIII, IX)
        $order = ['VII' => 1, 'VIII' => 2, 'IX' => 3];
        $allKelas = Siswa::select('kelas')->distinct()->pluck('kelas')->sort(function ($a, $b) use ($order) {
            $tA = $order[strtoupper(MataPelajaran::getTingkatFromKelas($a))] ?? 99;
            $tB = $order[strtoupper(MataPelajaran::getTingkatFromKelas($b))] ?? 99;
            if ($tA !== $tB) return $tA <=> $tB;
            return strnatcasecmp($a, $b);
        })->values();

        // Filter kelas jika tingkat dipilih
        $displayKelas = $allKelas;
        if ($selectedTingkat !== 'Semua') {
            $displayKelas = $allKelas->filter(function ($k) use ($selectedTingkat) {
                return MataPelajaran::getTingkatFromKelas($k) === $selectedTingkat;
            })->values();
        }

        // Query Mapel
        $mapelQuery = MataPelajaran::with('users')->orderBy('tingkat')->orderBy('nama_mapel');
        if ($selectedTingkat !== 'Semua') {
            $mapelQuery->where(function ($q) use ($selectedTingkat) {
                $q->where('tingkat', $selectedTingkat)->orWhere('tingkat', 'Semua')->orWhereNull('tingkat');
            });
        }
        if ($searchQuery !== '') {
            $mapelQuery->where(function ($q) use ($searchQuery) {
                $q->where('nama_mapel', 'like', "%{$searchQuery}%")
                  ->orWhere('kode_mapel', 'like', "%{$searchQuery}%")
                  ->orWhereHas('users', function ($u) use ($searchQuery) {
                      $u->where('name', 'like', "%{$searchQuery}%");
                  });
            });
        }
        $mapels = $mapelQuery->get();

        // Load seluruh kegiatan di semester & tahun ajaran ini
        $kegiatans = Kegiatan::withCount('nilais')
            ->where('semester', $selectedSemester)
            ->where('tahun_ajaran', $selectedTahunAjaran)
            ->get()
            ->groupBy(['mata_pelajaran_id', 'kelas']);

        $monitoringData = [];
        $totalMapelEvaluated = 0;
        $lengkapCount = 0;
        $sebagianCount = 0;
        $belumCount = 0;
        $totalKegiatanSekolah = 0;
        $totalNilaiSekolah = 0;
        $totalKelasTargetSekolah = 0;
        $totalKelasTerisiSekolah = 0;

        foreach ($mapels as $mapel) {
            $targetKelas = $mapel->filterKelasCollection($allKelas);
            if ($selectedTingkat !== 'Semua') {
                $targetKelas = $targetKelas->filter(function ($k) use ($selectedTingkat) {
                    return MataPelajaran::getTingkatFromKelas($k) === $selectedTingkat;
                })->values();
            }

            // Jika setelah filter tingkat, target kelas kosong, lewati
            if ($selectedTingkat !== 'Semua' && $targetKelas->isEmpty()) {
                continue;
            }

            $totalMapelEvaluated++;
            $filledKelas = [];
            $emptyKelas = [];
            $totalKegiatanMapel = 0;
            $totalNilaiMapel = 0;
            $breakdownJenis = ['Tugas' => 0, 'UH' => 0, 'UTS' => 0, 'UAS' => 0];

            foreach ($targetKelas as $kelas) {
                $kegs = $kegiatans->get($mapel->id, collect())->get($kelas, collect());
                if ($kegs->isNotEmpty()) {
                    $kCount = $kegs->count();
                    $nCount = $kegs->sum('nilais_count');
                    $totalKegiatanMapel += $kCount;
                    $totalNilaiMapel += $nCount;

                    foreach ($kegs as $kg) {
                        $j = $kg->jenis ?? 'Tugas';
                        if (isset($breakdownJenis[$j])) {
                            $breakdownJenis[$j]++;
                        }
                    }

                    $filledKelas[$kelas] = [
                        'kegiatan_count' => $kCount,
                        'nilai_count' => $nCount,
                        'kegiatans' => $kegs,
                    ];
                } else {
                    $emptyKelas[] = $kelas;
                }
            }

            $targetCount = count($targetKelas);
            $filledCount = count($filledKelas);
            $pctKeterisian = $targetCount > 0 ? round(($filledCount / $targetCount) * 100) : 0;

            $status = 'belum';
            if ($targetCount > 0 && $filledCount === $targetCount) {
                $status = 'lengkap';
                $lengkapCount++;
            } elseif ($filledCount > 0) {
                $status = 'sebagian';
                $sebagianCount++;
            } else {
                $belumCount++;
            }

            $totalKegiatanSekolah += $totalKegiatanMapel;
            $totalNilaiSekolah += $totalNilaiMapel;
            $totalKelasTargetSekolah += $targetCount;
            $totalKelasTerisiSekolah += $filledCount;

            $guruList = $mapel->users->pluck('name')->toArray();

            $item = [
                'mapel' => $mapel,
                'guru_list' => $guruList,
                'target_kelas' => $targetKelas,
                'filled_kelas' => $filledKelas,
                'empty_kelas' => $emptyKelas,
                'target_count' => $targetCount,
                'filled_count' => $filledCount,
                'pct_keterisian' => $pctKeterisian,
                'status' => $status,
                'total_kegiatan' => $totalKegiatanMapel,
                'total_nilai' => $totalNilaiMapel,
                'breakdown_jenis' => $breakdownJenis,
            ];

            if ($selectedStatus === 'semua' || $selectedStatus === $status) {
                $monitoringData[] = $item;
            }
        }

        $overallPct = $totalKelasTargetSekolah > 0
            ? round(($totalKelasTerisiSekolah / $totalKelasTargetSekolah) * 100)
            : 0;

        $stats = [
            'total_mapel' => $totalMapelEvaluated,
            'lengkap_count' => $lengkapCount,
            'sebagian_count' => $sebagianCount,
            'belum_count' => $belumCount,
            'total_kegiatan' => $totalKegiatanSekolah,
            'total_nilai' => $totalNilaiSekolah,
            'overall_pct' => $overallPct,
            'total_kelas_target' => $totalKelasTargetSekolah,
            'total_kelas_terisi' => $totalKelasTerisiSekolah,
        ];

        $daftarSemester = [
            1 => 'Semester 1 (Ganjil)',
            2 => 'Semester 2 (Genap)',
        ];
        $daftarTahunAjaran = ['2026/2027', '2025/2026'];

        return view('nilai.monitoring', compact(
            'monitoringData',
            'stats',
            'allKelas',
            'displayKelas',
            'selectedSemester',
            'selectedTahunAjaran',
            'selectedTingkat',
            'selectedStatus',
            'searchQuery',
            'daftarSemester',
            'daftarTahunAjaran'
        ));
    }
}

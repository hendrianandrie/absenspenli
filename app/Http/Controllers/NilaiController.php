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
        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();

        $selectedKelas = $request->get('kelas', $daftarKelas->first() ?? '');
        $selectedMapelId = $request->get('mata_pelajaran_id', $mapels->first()->id ?? null);

        $selectedMapel = $mapels->where('id', $selectedMapelId)->first();
        $siswas = collect();
        $kegiatans = collect();
        $rekapNilai = [];

        if ($selectedKelas && $selectedMapelId) {
            $siswas = Siswa::where('kelas', $selectedKelas)->orderBy('nama')->get();
            $kegiatans = Kegiatan::where('mata_pelajaran_id', $selectedMapelId)
                ->where('kelas', $selectedKelas)
                ->orderBy('tanggal')
                ->get();

            // Hitung Rata-rata Murni per siswa
            foreach ($siswas as $siswa) {
                $scores = [];
                foreach ($kegiatans as $kegiatan) {
                    $nilaiObj = Nilai::where('kegiatan_id', $kegiatan->id)
                        ->where('siswa_id', $siswa->id)
                        ->first();
                    $val = $nilaiObj ? $nilaiObj->nilai : null;
                    $scores[$kegiatan->id] = $val;
                }

                $validScores = array_filter($scores, fn ($n) => $n !== null);
                $rataRata = count($validScores) > 0 ? round(array_sum($validScores) / count($validScores), 1) : null;

                // Hitung predikat berdasarkan KKM mapel
                $kkm = $selectedMapel ? $selectedMapel->kkm : 75;
                $predikat = '-';
                if ($rataRata !== null) {
                    if ($rataRata >= 90) {
                        $predikat = 'A (Sangat Baik)';
                    } elseif ($rataRata >= 80) {
                        $predikat = 'B (Baik)';
                    } elseif ($rataRata >= $kkm) {
                        $predikat = 'C (Cukup / Tuntas)';
                    } else {
                        $predikat = 'D (Perlu Bimbingan)';
                    }
                }

                $rekapNilai[$siswa->id] = [
                    'scores' => $scores,
                    'rata_rata' => $rataRata,
                    'predikat' => $predikat,
                ];
            }
        }

        return view('nilai.index', compact(
            'daftarKelas',
            'mapels',
            'selectedKelas',
            'selectedMapelId',
            'selectedMapel',
            'siswas',
            'kegiatans',
            'rekapNilai'
        ));
    }

    public function createKegiatan(Request $request)
    {
        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();

        $selectedKelas = $request->get('kelas', $daftarKelas->first() ?? '');
        $selectedMapelId = $request->get('mata_pelajaran_id', $mapels->first()->id ?? null);

        $siswas = collect();
        if ($selectedKelas) {
            $siswas = Siswa::where('kelas', $selectedKelas)->orderBy('nama')->get();
        }

        return view('nilai.create_kegiatan', compact('daftarKelas', 'mapels', 'selectedKelas', 'selectedMapelId', 'siswas'));
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $kelas = $request->get('kelas');

        $query = Absensi::with('siswa')->orderBy('tanggal', 'desc');

        if ($kelas) {
            $query->whereHas('siswa', function ($q) use ($kelas) {
                $q->where('kelas', $kelas);
            });
        }

        $absensis = $query->get();
        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        return view('absensi.index', compact('absensis', 'daftarKelas', 'kelas'));
    }

    public function rekap(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggalSampai = $request->input('tanggal_sampai', date('Y-m-d'));
        $kelas = $request->input('kelas', '');

        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        $query = Absensi::with('siswa')
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSampai])
            ->orderBy('tanggal', 'desc');

        if (! empty($kelas)) {
            $query->whereHas('siswa', function ($q) use ($kelas) {
                $q->where('kelas', $kelas);
            });
        }

        $absensis = $query->get();

        $totalHadir = $absensis->where('status', 'Hadir')->count();
        $totalIzin = $absensis->where('status', 'Izin')->count();
        $totalSakit = $absensis->where('status', 'Sakit')->count();
        $totalAlpha = $absensis->where('status', 'Alpha')->count();

        return view('absensi.rekap', compact(
            'absensis', 'tanggalMulai', 'tanggalSampai', 'kelas', 'daftarKelas',
            'totalHadir', 'totalIzin', 'totalSakit', 'totalAlpha'
        ));
    }

    public function cetakPDF(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggalSampai = $request->input('tanggal_sampai', date('Y-m-d'));
        $kelas = $request->input('kelas', '');

        $query = Absensi::with('siswa')
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSampai])
            ->orderBy('tanggal', 'desc');

        if (! empty($kelas)) {
            $query->whereHas('siswa', function ($q) use ($kelas) {
                $q->where('kelas', $kelas);
            });
        }

        $absensis = $query->get();

        $totalHadir = $absensis->where('status', 'Hadir')->count();
        $totalIzin = $absensis->where('status', 'Izin')->count();
        $totalSakit = $absensis->where('status', 'Sakit')->count();
        $totalAlpha = $absensis->where('status', 'Alpha')->count();

        $pdf = Pdf::loadView('absensi.pdf', compact(
            'absensis', 'tanggalMulai', 'tanggalSampai', 'kelas',
            'totalHadir', 'totalIzin', 'totalSakit', 'totalAlpha'
        ));

        return $pdf->download('Rekap_Absensi_'.$tanggalMulai.'_sd_'.$tanggalSampai.'.pdf');
    }

    public function harian(Request $request)
    {
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $kelas = $request->get('kelas');
        $siswas = collect();

        if ($kelas) {
            $siswas = Siswa::where('kelas', $kelas)->orderBy('nama')->get();
        }

        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        return view('absensi.harian', compact('tanggal', 'kelas', 'siswas', 'daftarKelas'));
    }

    public function simpanHarian(Request $request)
    {
        $tanggal = $request->tanggal;
        $data = $request->absensi ?? [];

        foreach ($data as $siswa_id => $status) {
            Absensi::updateOrCreate(
                ['siswa_id' => $siswa_id, 'tanggal' => $tanggal],
                ['status' => $status]
            );
        }

        return redirect()->route('absensi.harian', ['tanggal' => $tanggal, 'kelas' => $request->kelas])
            ->with('success', 'Absensi tanggal '.\Carbon\Carbon::parse($tanggal)->format('d/m/Y').' berhasil disimpan!');
    }
}

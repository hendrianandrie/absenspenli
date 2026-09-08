<?php

namespace App\Http\Controllers;

use App\Imports\SiswaImport;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $selectedKelas = $request->get('kelas');
        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        $query = Siswa::query();
        if ($selectedKelas) {
            $query->where('kelas', $selectedKelas);
        }

        $siswas = $query->orderBy('kelas')->orderBy('nama')->get();

        return view('siswa.index', compact('siswas', 'daftarKelas', 'selectedKelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|unique:siswas,nis,'.$request->id,
            'nama' => 'required|string|max:150',
            'kelas' => 'required|string|max:20',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        Siswa::create($request->all());

        return back()->with('success', 'Data siswa berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        $nama = $siswa->nama;
        $siswa->delete();

        return back()->with('success', 'Data siswa "'.$nama.'" berhasil dihapus!');
    }

    public function destroyKelas(Request $request)
    {
        $request->validate([
            'kelas' => 'required|string',
        ]);

        $kelas = $request->kelas;
        $count = Siswa::where('kelas', $kelas)->count();

        if ($count === 0) {
            return back()->with('error', 'Tidak ada data siswa untuk kelas '.$kelas);
        }

        Siswa::where('kelas', $kelas)->delete();

        return back()->with('success', 'Seluruh data siswa kelas '.$kelas.' ('.$count.' siswa) berhasil dihapus!');
    }

    public function importForm()
    {
        return view('siswa.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            $totalBefore = Siswa::count();
            Excel::import(new SiswaImport, $request->file('file'));
            $totalAfter = Siswa::count();

            return redirect()->route('siswa.index')->with('success', 'Berhasil mengimpor data siswa! Saat ini terdapat total '.$totalAfter.' siswa terdaftar dalam sistem.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor data: '.$e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_data_siswa.csv"',
        ];

        $content = "nis,nama,kelas,jenis_kelamin\n";
        $content .= "8001,Aditya Pratama,7A,L\n";
        $content .= "8002,Budi Santoso,7A,L\n";
        $content .= "8003,Citra Lestari,7A,P\n";

        return response($content, 200, $headers);
    }
}

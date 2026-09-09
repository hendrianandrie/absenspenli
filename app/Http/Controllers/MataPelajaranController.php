<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isGuru = $user && $user->role === 'guru';

        if ($isGuru && $user->mata_pelajaran_id) {
            $mapels = MataPelajaran::where('id', $user->mata_pelajaran_id)->get();
        } elseif ($isGuru) {
            $mapels = collect();
        } else {
            $mapels = MataPelajaran::orderBy('nama_mapel')->get();
        }

        return view('mapel.index', compact('mapels', 'isGuru'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isGuru = $user && $user->role === 'guru';

        // Jika guru mencoba menambah mapel baru tanpa ID mapel diampu
        if ($isGuru && ! $request->id) {
            return redirect()->route('mapel.index')->with('error', 'Hanya Admin yang dapat menambah mata pelajaran baru.');
        }

        // Jika guru mencoba mengedit mapel lain yang bukan diampunya
        if ($isGuru && $request->id && $request->id != $user->mata_pelajaran_id) {
            return redirect()->route('mapel.index')->with('error', 'Anda hanya dapat mengubah KKM & Bobot mata pelajaran yang Anda ampu.');
        }

        $request->validate([
            'kode_mapel' => 'required|string|max:50|unique:mata_pelajarans,kode_mapel,'.$request->id,
            'nama_mapel' => 'required|string|max:100',
            'tingkat' => 'required|string|in:7,8,9,Semua',
            'kkm' => 'required|integer|min:0|max:100',
            'bobot_tugas' => 'nullable|integer|min:0|max:100',
            'bobot_uh' => 'nullable|integer|min:0|max:100',
            'bobot_uts' => 'nullable|integer|min:0|max:100',
            'bobot_uas' => 'nullable|integer|min:0|max:100',
        ]);

        MataPelajaran::updateOrCreate(
            ['id' => $request->id],
            [
                'kode_mapel' => strtoupper($request->kode_mapel),
                'nama_mapel' => $request->nama_mapel,
                'tingkat' => $request->tingkat ?? 'Semua',
                'kkm' => $request->kkm,
                'bobot_tugas' => $request->input('bobot_tugas', 20),
                'bobot_uh' => $request->input('bobot_uh', 30),
                'bobot_uts' => $request->input('bobot_uts', 25),
                'bobot_uas' => $request->input('bobot_uas', 25),
            ]
        );

        return redirect()->route('mapel.index')->with('success', 'Mata Pelajaran & Bobot Penilaian berhasil disimpan!');
    }

    public function destroy($id)
    {
        if (auth()->user()->role === 'guru') {
            return redirect()->route('mapel.index')->with('error', 'Hanya Admin yang dapat menghapus mata pelajaran.');
        }

        $mapel = MataPelajaran::findOrFail($id);
        $mapel->delete();

        return redirect()->route('mapel.index')->with('success', 'Mata Pelajaran berhasil dihapus!');
    }
}

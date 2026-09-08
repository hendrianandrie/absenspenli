<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();

        return view('mapel.index', compact('mapels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_mapel' => 'required|string|max:50|unique:mata_pelajarans,kode_mapel,'.$request->id,
            'nama_mapel' => 'required|string|max:100',
            'kkm' => 'required|integer|min:0|max:100',
        ]);

        MataPelajaran::updateOrCreate(
            ['id' => $request->id],
            [
                'kode_mapel' => strtoupper($request->kode_mapel),
                'nama_mapel' => $request->nama_mapel,
                'kkm' => $request->kkm,
            ]
        );

        return redirect()->route('mapel.index')->with('success', 'Mata Pelajaran berhasil disimpan!');
    }

    public function destroy($id)
    {
        $mapel = MataPelajaran::findOrFail($id);
        $mapel->delete();

        return redirect()->route('mapel.index')->with('success', 'Mata Pelajaran berhasil dihapus!');
    }
}

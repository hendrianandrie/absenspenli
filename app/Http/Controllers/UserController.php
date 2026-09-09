<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Hanya Admin yang dapat mengelola akun pengguna.');
        }

        $users = User::with('mataPelajaran')->orderBy('role')->orderBy('name')->get();
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();
        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        return view('users.index', compact('users', 'mapels', 'daftarKelas'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Hanya Admin yang dapat mengelola akun pengguna.');
        }

        $userId = $request->id;

        $request->validate([
            'name' => 'required|string|max:100|unique:users,name,'.$userId,
            'email' => 'required|email|max:150|unique:users,email,'.$userId,
            'password' => $userId ? 'nullable|string|min:6' : 'required|string|min:6',
            'role' => 'required|in:admin,piket,guru',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajarans,id',
            'kelas_diampu' => 'nullable|array',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'mata_pelajaran_id' => $request->role === 'guru' ? $request->mata_pelajaran_id : null,
            'kelas_diampu' => $request->role === 'guru' ? ($request->kelas_diampu ?? []) : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        User::updateOrCreate(['id' => $userId], $data);

        $msg = $userId ? 'Data akun pengguna berhasil diperbarui!' : 'Akun pengguna baru berhasil ditambahkan!';
        return redirect()->route('users.index')->with('success', $msg);
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Hanya Admin yang dapat mengelola akun pengguna.');
        }

        if (auth()->id() == $id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan!');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dihapus!');
    }
}

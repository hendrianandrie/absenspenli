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

        $users = User::with(['mataPelajaran', 'mataPelajarans'])->orderBy('role')->orderBy('name')->get();
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();
        $rawKelas = Siswa::select('kelas')->distinct()->pluck('kelas');
        $daftarKelas = $rawKelas->sort(function ($a, $b) {
            $order = ['VII' => 1, 'VIII' => 2, 'IX' => 3];
            preg_match('/^(VIII|VII|IX|\d+)/i', $a, $mA);
            preg_match('/^(VIII|VII|IX|\d+)/i', $b, $mB);
            $tA = $order[strtoupper($mA[1] ?? '')] ?? 99;
            $tB = $order[strtoupper($mB[1] ?? '')] ?? 99;
            if ($tA !== $tB) return $tA <=> $tB;
            return strnatcasecmp($a, $b);
        })->values();

        return view('users.index', compact('users', 'mapels', 'daftarKelas'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Hanya Admin yang dapat mengelola akun pengguna.');
        }

        $userId = $request->id;

        $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|alpha_dash|unique:users,username,'.$userId,
            'nip' => 'nullable|string|max:35',
            'email' => 'required|email|max:150|unique:users,email,'.$userId,
            'password' => $userId ? 'nullable|string|min:6' : 'required|string|min:6',
            'role' => 'required|in:admin,piket,guru',
            'mata_pelajaran_ids' => 'nullable|array',
            'mata_pelajaran_ids.*' => 'exists:mata_pelajarans,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajarans,id',
            'kelas_diampu' => 'nullable|array',
        ]);

        $mapelIds = [];
        if ($request->role === 'guru') {
            if ($request->has('mata_pelajaran_ids')) {
                $mapelIds = array_values(array_filter((array) $request->mata_pelajaran_ids));
            } elseif ($request->filled('mata_pelajaran_id')) {
                $mapelIds = [(int) $request->mata_pelajaran_id];
            }
        }
        $primaryMapelId = !empty($mapelIds) ? $mapelIds[0] : null;

        $data = [
            'name' => $request->name,
            'username' => strtolower(trim($request->username)),
            'nip' => $request->filled('nip') ? trim($request->nip) : null,
            'email' => $request->email,
            'role' => $request->role,
            'mata_pelajaran_id' => $primaryMapelId,
            'kelas_diampu' => $request->role === 'guru' ? ($request->kelas_diampu ?? []) : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user = User::updateOrCreate(['id' => $userId], $data);

        if ($request->role === 'guru') {
            $user->mataPelajarans()->sync($mapelIds);
        } else {
            $user->mataPelajarans()->detach();
        }

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

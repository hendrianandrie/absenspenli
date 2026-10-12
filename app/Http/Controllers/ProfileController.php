<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan formulir edit profil dan ubah kata sandi
     */
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load(['mataPelajarans', 'mataPelajaran', 'siswa', 'waliKelas']);

        return view('profile.edit', compact('user'));
    }

    /**
     * Simpan pembaruan profil dan/atau ganti kata sandi
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
        ];

        // Jika bukan siswa, username & NIP dapat diperbarui
        if ($user->role !== 'siswa') {
            $rules['username'] = ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users')->ignore($user->id)];
            $rules['nip'] = ['nullable', 'string', 'max:35'];
        }

        // Validasi kata sandi lama & kata sandi baru jika pengguna ingin mengubah password
        if ($request->filled('password') || $request->filled('current_password') || $request->filled('password_confirmation')) {
            $rules['current_password'] = ['required', 'string'];
            $rules['password'] = ['required', 'string', 'min:6', 'confirmed'];
        }

        $messages = [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format penulisan email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar untuk akun lain.',
            'username.required' => 'Username login wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan akun lain.',
            'username.alpha_dash' => 'Username hanya boleh terdiri dari huruf, angka, tanda minus (-), dan garis bawah (_).',
            'current_password.required' => 'Kata sandi lama wajib diisi untuk melakukan perubahan kata sandi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sesuai.',
        ];

        $validated = $request->validate($rules, $messages);

        // Jika ada pergantian kata sandi, verifikasi kecocokan kata sandi lama
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'Kata sandi saat ini (lama) yang Anda masukkan salah.'])
                    ->withInput($request->except(['current_password', 'password', 'password_confirmation']));
            }

            $user->password = Hash::make($request->password);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($user->role !== 'siswa') {
            $user->username = strtolower(trim($validated['username']));
            if ($request->has('nip')) {
                $user->nip = $request->filled('nip') ? trim($request->nip) : null;
            }
        } else {
            // Jika akun siswa dan terhubung dengan data siswa, sinkronkan nama ke data siswa
            if ($user->siswa) {
                $user->siswa->update(['nama' => $validated['name']]);
            }
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profil dan informasi akun Anda berhasil diperbarui!');
    }
}

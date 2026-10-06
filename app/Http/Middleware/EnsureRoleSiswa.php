<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleSiswa
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'siswa') {
            abort(403, 'Akses terbatas untuk akun Siswa.');
        }

        return $next($request);
    }
}

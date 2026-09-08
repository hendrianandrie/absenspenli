<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckNotPiket
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'piket') {
            return redirect()->route('dashboard')->with('error', 'Akun piket hanya memiliki akses ke Sistem Absensi.');
        }

        return $next($request);
    }
}

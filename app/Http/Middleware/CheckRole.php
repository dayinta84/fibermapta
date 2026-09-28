<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Mengecek apakah user yang login memiliki salah satu role yang diizinkan.
     *
     * Contoh pemakaian di route:
     * ->middleware('role:superadmin')
     * ->middleware('role:superadmin,admin')
     * ->middleware('role:superadmin,admin,teknisi')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Anda harus login terlebih dahulu.');
        }

        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}

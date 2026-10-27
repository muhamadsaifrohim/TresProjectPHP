<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Membatasi route berdasarkan kolom `role` di tabel users.
// Contoh pemakaian di routes: ->middleware('role:admin')
// Beberapa role sekaligus: ->middleware('role:admin,moderator')
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak punya akses untuk aksi ini.');
        }

        return $next($request);
    }
}

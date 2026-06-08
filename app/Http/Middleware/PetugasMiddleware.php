<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PetugasMiddleware
{
    /**
     * Handle an incoming request.
     * Allows users with 'admin' or 'petugas' role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || (! $request->user()->isAdmin() && ! $request->user()->isPetugas())) {
            abort(403, 'Akses ditolak. Hanya admin atau petugas yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\KepegawaianAkses;
use Symfony\Component\HttpFoundation\Response;

class PresensiPegawai
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('user');
        $akses = KepegawaianAkses::where('id_pegawai', $user->id)->first();
        if ($akses) {
            return $next($request);
        }
        abort(403);
    }
}

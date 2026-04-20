<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDept
{

    public function handle(Request $request, Closure $next, string ...$depts): Response
    {
        if (auth()->user()->role === 'admin') {
            return $next($request);
        }

        if (!in_array(auth()->user()->dept, $depts)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}

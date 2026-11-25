<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /** Yalnızca Cardino yöneticisi geçebilir. */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() && $request->user()->isAdmin(), 403, 'Bu sayfa yalnızca yöneticiye açık.');

        return $next($request);
    }
}

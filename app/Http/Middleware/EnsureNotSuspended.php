<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isSuspended()) {
            return redirect('/suspended');
        }

        return $next($request);
    }
}

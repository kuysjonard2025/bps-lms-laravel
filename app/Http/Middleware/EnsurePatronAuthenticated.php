<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatronAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Session::has('patron_session_id')) {
            return redirect()->route('patron.login');
        }

        return $next($request);
    }
}

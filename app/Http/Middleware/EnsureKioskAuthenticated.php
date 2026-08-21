<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKioskAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('kiosk_authenticated')) {
            return redirect()->route('kiosk.login');
        }

        return $next($request);
    }
}

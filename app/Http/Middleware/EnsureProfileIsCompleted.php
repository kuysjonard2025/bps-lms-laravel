<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $isIncomplete = empty($user->first_name) ||
                            empty($user->middle_name) ||
                            empty($user->last_name) ||
                            empty($user->address) ||
                            empty($user->contact_number);

            if ($isIncomplete && ! $request->routeIs('profile.complete')) {
                return redirect()->route('profile.complete');
            }
        }

        return $next($request);
    }
}

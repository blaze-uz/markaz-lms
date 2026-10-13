<?php

namespace App\Http\Middleware;

use App\Models\UserTelegram;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebAppTeachersAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //check for auth if user not auth redirect to login
        if (auth()->check())
            return $next($request);
        return redirect()->route('inertia-teachers.login');
    }
}

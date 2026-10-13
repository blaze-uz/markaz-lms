<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //check lang session
        if (session()->has('lang')) {
            //get lang session
            $lang = session()->get('lang');
            //set app locale
            app()->setLocale($lang);
        } else {
            //set app locale
            app()->setLocale(config('app.locale'));
        }

        return $next($request);
    }
}

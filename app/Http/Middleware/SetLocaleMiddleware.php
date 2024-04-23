<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('api')::check()) {
            $userLang = Auth::user()->language; // Assuming 'lang' is the column name in the 'users' table
            if ($userLang) {
                App::setLocale($userLang);
            }
        }

        return $next($request);
    }
}

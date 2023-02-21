<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MustBeLoggedIn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->check()){
            return $next($request);//$next passes the request to the next step after middleware
        }
        return redirect('/')->with('failure', 'You must be logged in');
    }
}

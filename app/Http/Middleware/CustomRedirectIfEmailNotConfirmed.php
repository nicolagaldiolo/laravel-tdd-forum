<?php

namespace App\Http\Middleware;

use Closure;

// conferma account manuale (solo per scopi didattici, altrimenti usare funzioanlità built-in laravel)

class CustomRedirectIfEmailNotConfirmed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if(! $request->user()->confirmed){
            return redirect('/threads')
                ->with('flash', 'You must confirm first your email address');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
class TrackingDataDebug
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
        if(Route::getCurrentRoute()->getPrefix() == 'admin'){
            if($request->ajax() || Str::upper($request->getMethod()) == 'POST'){
                Log::channel('tracking')->info(Auth::user()->name .': '. $request->getMethod());
                Log::channel('tracking')->info(json_encode($request->all()));
                Log::channel('tracking')->info("----------------");
            }
        }
        return $next($request);
    }
}

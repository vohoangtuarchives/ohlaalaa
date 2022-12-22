<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Support\Facades\Log;

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

        if($request->ajax()){
            Log::debug("Tracking Ajax: ". json_encode($request->all()));
        }
        if(strtolower($request->getMethod()) == 'post'){
            Log::debug("Tracking Post: ". json_encode($request->all()));
        }

        return $next($request);
    }
}

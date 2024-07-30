<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AddAcceptHeaderToAPIRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$request->headers->has('Accept')) {
            //Log::debug("Added Accept header to missing API request, {request}", ['request' => $request]);
            $request->headers->set('Accept', 'application/json');
        }
        return $next($request);
    }
}

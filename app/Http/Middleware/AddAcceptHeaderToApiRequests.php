<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AddAcceptHeaderToApiRequests
{
    /**
     * Add an Accept header to API requests
     * Laravel will treat any request as a text/html response instead of API json
     * response if it doesn't find this.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            !$request->headers->has('Accept')
            || $request->headers->get('Accept') !== 'application/json'
        ) {
            //Log::info("Added Accept header to request");
            $request->headers->set('Accept', 'application/json');
        } else {
            //Log::info($request->headers);
        }
        return $next($request);
    }
}

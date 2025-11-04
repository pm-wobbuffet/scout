<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AddRequestIdToApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a unique request Id for this request, if needed for debugging
        // Add it to any Log messages generated on this request
        $requestId = (string) Str::uuid();
        Log::withContext([
            'scout-request-id' => $requestId
        ]);

        $response = $next($request);
        // Send the response Id back to the client in case they want to submit bug reports, etc
        // and reference a particular Request
        $response->headers->set('Scout-Request-Id', $requestId);
        return $response;
    }
}

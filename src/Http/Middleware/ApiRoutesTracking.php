<?php

namespace Rosalana\Tracker\Http\Middleware;

use Illuminate\Http\Request;
use Rosalana\Tracker\Facades\Tracker;
use Symfony\Component\HttpFoundation\Response;

class ApiRoutesTracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        Tracker::emitRoute(
            group: 'api',
            method: $request->method(),
            path: $request->path(),
            ip: $request->ip(),
        );

        return $next($request);
    }
}

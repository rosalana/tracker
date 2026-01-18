<?php

namespace Rosalana\Tracer\Http\Middleware;

use Illuminate\Http\Request;
use Rosalana\Tracer\Facades\Tracer;
use Symfony\Component\HttpFoundation\Response;

class WebRoutesTracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        Tracer::emitRoute(
            group: 'web',
            method: $request->method(),
            path: $request->path(),
            ip: $request->ip(),
        );

        return $next($request);
    }
}

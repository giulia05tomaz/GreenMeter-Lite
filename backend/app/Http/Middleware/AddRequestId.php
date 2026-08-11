<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AddRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $candidate = $request->header('X-Request-ID');
        $requestId = is_string($candidate) && preg_match('/^[A-Za-z0-9._-]{1,128}$/', $candidate)
            ? $candidate
            : (string) Str::uuid();
        $request->headers->set('X-Request-ID', $requestId);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoIsReadOnly
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        if ($request->user()?->is_demo) {
            return response()->json([
                'message' => 'Contas de demonstração são somente leitura.',
                'error' => 'demo_read_only',
            ], 403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteApiKeyIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (! $apiKey) {
            return response()->json([
                'message' => 'API key is required.',
            ], 401);
        }

        $site = Site::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (! $site) {
            return response()->json([
                'message' => 'Invalid or inactive API key.',
            ], 401);
        }

        $request->merge(['site' => $site]);

        return $next($request);
    }
}

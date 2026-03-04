<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AllowIframeEmbedding
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Remove X-Frame-Options to allow iframe embedding
        $response->headers->remove('X-Frame-Options');

        // Add CSP frame-ancestors to allow embedding from same origin
        // This is the modern replacement for X-Frame-Options
        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'self'",
            false
        );

        return $response;
    }
}

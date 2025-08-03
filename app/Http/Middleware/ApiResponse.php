<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add API response headers
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('X-API-Version', 'v1');
        $response->headers->set('X-Powered-By', 'Mini ERP System');

        // Note: CORS is already handled by Laravel's built-in CORS middleware
        // which uses the configuration from config/cors.php

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class HandleApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        // If it's an API request expecting JSON, proceed normally
        if ($request->expectsJson()) {
            return $next($request);
        }

        // For browser requests to API endpoints
        if ($request->is('api/*')) {
            // If not authenticated and trying to access protected routes
            if (!Auth::check() && !$request->is('api/admins/login')) {
                return redirect()->route('admin.login');
            }
        }

        return $next($request);
    }
}
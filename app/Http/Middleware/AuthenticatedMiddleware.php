<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized. Please login.'], 401);
        }

        // Check if the user is either an Admin or a regular User
        if (get_class($user) === 'App\Models\Admin' || get_class($user) === 'App\Models\User') {
            return $next($request);
        }

        return response()->json(['message' => 'Access denied. Invalid user type.'], 403);
    }
}

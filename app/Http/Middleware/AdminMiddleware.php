<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('sanctum')->user() && get_class(Auth::guard('sanctum')->user()) === 'App\Models\Admin') {
            return $next($request);
        }

        return response()->json(['message' => 'Access denied. Admins only.'], 403);
    }
}


<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('sanctum')->user();

        if ($user && $user instanceof Admin) {
            return $next($request);
        }

        return response()->json(['message' => 'Access denied. Admins only.'], 403);
    }
}


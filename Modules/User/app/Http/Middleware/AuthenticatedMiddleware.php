<?php

namespace Modules\User\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\app\Models\Admin;
use Modules\User\app\Models\User;
use Modules\Business\app\Models\Business;

class AuthenticatedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized. Please login.'], 401);
        }

        // Check if the user is either an Admin, a regular User, or a BusinessAccount
        if ($user instanceof Admin || $user instanceof User || $user instanceof Business) {
            return $next($request);
        }

        return response()->json(['message' => 'Access denied. Invalid user type.'], 403);
    }
}

<?php

namespace Modules\Admin\app\Http\Controllers;

use Modules\Admin\app\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\app\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = Admin::where('email', $credentials['email'])->first();

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $admin->createToken('admin_auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Admin login successful',
            'token' => $token
        ]);
    }
    public function logout(Request $request)
    {
        $user = $request->user();

        // Check if the authenticated user is an Admin
        if ($user instanceof \Modules\Admin\app\Models\Admin) {
            $user->tokens()->delete();
            return response()->json(['message' => 'Admin logged out successfully']);
        }

        return response()->json(['message' => 'Unauthorized. Only admins can log out through this endpoint.'], 403);
    }
}

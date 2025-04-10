<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessAccount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class BusinessAccountController extends Controller
{
    public function index()
    {
        $businessAccounts = BusinessAccount::all();
        return response()->json($businessAccounts);
    }

    public function store(Request $request)
    {
        $businessAccount = BusinessAccount::create($request->all());
        return response()->json($businessAccount, 201);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:business_accounts'],
            'password' => ['required', Password::defaults()],
            'bio' => ['nullable', 'string'],
            'profile_picture' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        $business = BusinessAccount::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'bio' => $request->bio,
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $business->profile_picture = $path;
            $business->save();
        }

        $token = $business->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Business account created successfully',
            'business' => $business,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $business = BusinessAccount::where('email', $request->email)->first();

        if (!$business || !Hash::check($request->password, $business->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $business->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'business' => $business,
            'token' => $token
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:business_accounts,email,' . $request->user()->id],
            'bio' => ['nullable', 'string'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        $business = $request->user();

        if ($request->has('name')) {
            $business->name = $request->name;
        }

        if ($request->has('email')) {
            $business->email = $request->email;
        }

        if ($request->has('bio')) {
            $business->bio = $request->bio;
        }

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $business->profile_picture = $path;
        }

        $business->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'business' => $business
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}

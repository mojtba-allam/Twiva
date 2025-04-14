<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessAccount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\BusinessAccountResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BusinessAccountController extends Controller
{
    public function index()
    {
        $businessAccounts = BusinessAccount::paginate(10);
        return BusinessAccountResource::collection($businessAccounts);
    }

    public function store(Request $request)
    {
        $businessAccount = BusinessAccount::create($request->all());
        return new BusinessAccountResource($businessAccount);
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
            'token' => $token
        ]);
    }

    public function profile(Request $request, $id)
    {
        try {
            // Check if request is from an admin
            $user = auth()->guard('sanctum')->user();
            $isAdmin = $user && $user instanceof \App\Models\Admin;

            // Get the logged-in business account or find by ID if different
            if ($request->user() && $request->user()->id == $id) {
                $business = $request->user();
            } else {
                $business = BusinessAccount::findOrFail($id);
            }

            if ($isAdmin) {
                // For admin, load all products without specifying admin relationship
                $business->load('products');
            } else {
                // For non-admin, load only approved products
                $business->load(['products' => function($query) {
                    $query->where('status', 'approved');
                }]);
            }

            return new BusinessAccountResource($business);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Business account not found'
            ], 404);
        }
    }

    public function updateProfile(Request $request)
    {
        $business = $request->user('sanctum');
        if (!$business || !($business instanceof BusinessAccount)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:business_accounts,email,' . $business->id],
            'bio' => ['nullable', 'string'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

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
            'business' => new BusinessAccountResource($business)
        ]);
    }

    public function logout(Request $request)
    {
        $business = $request->user('sanctum');
        if ($business && $business instanceof BusinessAccount) {
            $business->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        }

        return response()->json(['message' => 'No active session found'], 400);
    }

    public function show($id)
    {
        // Check if request is from an admin
        $user = auth()->guard('sanctum')->user();
        $isAdmin = $user && $user instanceof \App\Models\Admin;

        if ($isAdmin) {
            // For admin, load all products without specifying admin relationship
            $business = BusinessAccount::with('products')->findOrFail($id);
        } else {
            // For non-admin, load only approved products
            $business = BusinessAccount::with(['products' => function($query) {
                $query->where('status', 'approved');
            }])->findOrFail($id);
        }

        return new BusinessAccountResource($business);
    }
}

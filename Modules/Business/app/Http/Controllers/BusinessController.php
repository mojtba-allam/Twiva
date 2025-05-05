<?php

namespace Modules\Business\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Business\app\Models\Business;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Business\app\Http\Resources\BusinessResource;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = Business::paginate(10);

        return BusinessResource::collection($businesses);
    }

    public function store(Request $request)
    {
        $business = Business::create($request->all());
        return new BusinessResource($business);
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

        $business = Business::create([
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

        $business = Business::where('email', $request->email)->first();

        if (!$business || !Hash::check($request->password, $business->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $business->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'id' => $business->id,
            'token' => $token
        ]);
    }

    public function profile(Request $request, $id)
    {
        try {
            // Check if request is from an admin
            $user = auth()->guard('sanctum')->user();
            $isAdmin = $user && $user instanceof \Modules\Admin\app\Models\Admin;

            // Get the logged-in business account or find by ID if different
            if ($request->user() && $request->user()->id == $id) {
                $business = $request->user();
            } else {
                $business = Business::findOrFail($id);
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

            $businessData = (new BusinessResource($business))->resolve($request);

            // Check if products exist
            if (empty($businessData['data']['products'])) {
                $businessData['products'] = "No products available yet.";
            }

            return response()->json($businessData);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Business account not found'
            ], 404);
        }
    }

    public function updateProfile(Request $request)
    {
        $business = $request->user('sanctum');
        if (!$business || !($business instanceof Business)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:business_accounts,email,' . $business->id],
            'bio' => ['nullable', 'string'],
            'profile_picture' => ['nullable', 'string']
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
        } elseif ($request->has('profile_picture')) {
            $business->profile_picture = $request->profile_picture;
        }

        $business->save();

        return response()->json([
            'message' => 'Profile updated successfully'
        ]);
    }

    public function logout(Request $request)
    {
        $business = $request->user('sanctum');
        if ($business && $business instanceof Business) {
            $business->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        }

        return response()->json(['message' => 'No active session found'], 400);
    }

    public function show($id)
{
    try {
        $business = Business::with('products')->findOrFail($id);
        return new BusinessResource($business);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Business not found'
        ], 404);
    }
}

    public function myProducts(Request $request)
    {
        try {
            $business = $request->user('sanctum');

                if (!$business || !($business instanceof Business)) {
                return response()->json([
                    'message' => 'Unauthorized. Please login as a business account.'
                ], 403);
            }

            $products = $business->products()
                ->select('id', 'title', 'image_url', 'status')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($product) {
                    return [
                        'title' => $product->title,
                        'image' => $product->image_url,
                        'status' => $product->status,
                        'url' => url("/api/products/{$product->id}")
                    ];
                });

            if ($products->isEmpty()) {
                return response()->json([
                    'message' => 'You have no products yet.'
                ]);
            }

            return response()->json([
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving products'
            ], 500);
        }
    }
}

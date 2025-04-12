<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get authenticated user from request
        $user = Auth::guard('sanctum')->user();

        // If no user is authenticated or user is not an admin, return 404
        if (!$user || !($user instanceof Admin)) {
            abort(404);
        }

        // Return the list of users with pagination for admins
        return UserResource::collection(User::paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->save();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Get authenticated user from Auth facade
        $user = Auth::guard('sanctum')->user();

        // If no user is authenticated, return 404
        if (!$user) {
            abort(404);
        }

        // Check if the authenticated user is an admin
        $isAdmin = $user instanceof Admin;
        $isBusinessAccount = $user instanceof \App\Models\BusinessAccount;
        $isSameUser = ($user instanceof User) && ($user->id == $id);

        // Set up base query
        $query = User::query();

        // Load orders for admins and the user themselves
        if ($isAdmin || $isSameUser) {
            $query->with('orders');
        }

        // Get the requested user
        $userData = $query->find($id);

        if (!$userData) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check authorization
        if (!$isAdmin && !$isBusinessAccount && !$isSameUser && !($user instanceof User)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Build the response
        $response = [
            'id' => $userData->id,
            'name' => $userData->name,
            'image' => $userData->image ?? null,
        ];

        // Add additional data for admins, business accounts, the user themselves, or other regular users
        if ($isAdmin || $isBusinessAccount || $isSameUser || ($user instanceof User)) {
            $response = array_merge($response, [
                'email' => $userData->email,
                'bio' => $userData->bio ?? null,
            ]);

            // Include created_at and updated_at for admins and the user themselves
            if ($isAdmin || $isSameUser) {
                $response = array_merge($response, [
                    'created_at' => $userData->created_at,
                    'updated_at' => $userData->updated_at,
                ]);
            }

            // Include orders data only for admins and the user themselves
            if (($isAdmin || $isSameUser) && $userData->orders) {
                if ($userData->orders->isEmpty()) {
                    $response['orders_message'] = 'User has no orders.';
                    $response['orders'] = [];
                } else {
                    $response['orders'] = $userData->orders->map(function($order) {
                        return [
                            'id' => $order->id,
                            'total_price' => $order->total_price,
                            'status' => $order->status,
                            'created_at' => $order->created_at,
                            'url' => route('orders.show', $order->id)
                        ];
                    });
                }
            }
        }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->image = $request->image;
        $user->bio = $request->bio;
        $user->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        $user->delete();
    }

    public function edit(Request $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if($user->id !== $request->user()->id){
            return response()->json(['message' => 'You are not authorized to update this user'], 403);
        }
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|string|min:8',
            ]);

            // Prepare the data for update
            $updateData = $request->only(['name', 'email']);

            // Handle password separately
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'message' => 'User updated successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('User update failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

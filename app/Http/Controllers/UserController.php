<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::paginate(5);
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
    public function show(Request $request ,string $id)
    {
        try {
            $user = User::findOrFail($id);
            if($user->id !== $request->user()->id){
                return response()->json(['message' => 'You are not authorized to view this user'], 403);
            }
            return response()->json([
                'user' => $user
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching user: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while fetching the user'
            ], 500);
        }
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
                'user' => $user,
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

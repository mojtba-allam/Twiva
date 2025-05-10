<?php

namespace Modules\Category\app\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Category\app\Models\Category;
use Modules\Category\app\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Category\app\Http\Controllers\Controller;
class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CategoryResource::collection(Category::paginate(5));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $category = new Category();
        $category->name = $request->name;
        $category->admin_id = $admin->id;
        $category->created_at = now();
        $category->updated_at = now();
        $category->save();

        return response()->json([
            'message' => 'Category created successfully',
            'status' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Check which guard is authenticated
            $user = null;
            $guard = null;

            if (Auth::guard('admin')->check()) {
                $user = Auth::guard('admin')->user();
                $guard = 'admin';
            } elseif (Auth::guard('business')->check()) {
                $user = Auth::guard('business')->user();
                $guard = 'business';
            } elseif (Auth::guard('user')->check()) {
                $user = Auth::guard('user')->user();
                $guard = 'user';
            }

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Find the category
            $category = Category::query();

            // Load relationships based on guard
            if ($guard === 'admin') {
                $category->with(['Product' => function($query) {
                    $query->with('business');
                }]);
            } elseif ($guard === 'business') {
                $category->with(['Product' => function($query) use ($user) {
                    $query->where(function($q) use ($user) {
                        $q->where('status', 'approved')
                          ->orWhere('business_account_id', $user->id);
                    })->with('business');
                }]);
            } else {
                $category->with(['Product' => function($query) {
                    $query->where('status', 'approved')
                          ->with('business');
                }]);
            }

            $category = $category->findOrFail($id);

            return new CategoryResource($category);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }
    }



    /**
     * Edit the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $category = Category::findOrFail($id);
            $category->name = $request->name;
            $category->updated_at = now();
            $category->save();

            return response()->json([
                'message' => 'Category updated successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $admin = Auth::guard('admin')->user();
        try {
            $category = Category::findOrFail($id);
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }
}

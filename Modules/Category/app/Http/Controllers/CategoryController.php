<?php

namespace Modules\Category\app\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Category\app\Models\Category;
use Modules\Category\app\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
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
        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $isAdmin = Auth::guard('admin')->check();

            if ($isAdmin) {
                // Admin sees all products
                $category = Category::with('Product')->findOrFail($id);
            } else {
                // Regular users and business accounts see only approved products
                $category = Category::with(['Product' => function($query) {
                    $query->where('status', 'approved');
                }])->findOrFail($id);
            }

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
    public function edit(Request $request, string $id)
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

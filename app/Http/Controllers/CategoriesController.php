<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Categories::paginate(5);
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
        $category = new Categories();
        $category->name = $request->name;
        $category->admin_id = $admin->id;
        $category->created_at = now();
        $category->updated_at = now();
        $category->save();
        return response()->json(['message' => 'Category created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Categories::find($id);
        return $category;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Categories::find($id);
        $category->name = $request->name;
        $category->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request ,string $id)
    {
        $admin = Auth::guard('admin')->user();
        try {
            $category = Categories::findOrFail($id);
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully'], 200);
            } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
            }
            // if ($category->admin_id !== $admin->id) {
            // return response()->json(['message' => 'Unauthorized'], 403);
            // }
    }
}

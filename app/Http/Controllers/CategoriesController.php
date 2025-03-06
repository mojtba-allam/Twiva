<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
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
        $category = new Categories();
        $category->name = $request->name;
        $category->admin_id = $request->admin_id;
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
    public function destroy(string $id)
    {
        $category = Categories::find($id);
        $category->delete();
    }
}

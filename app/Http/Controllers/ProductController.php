<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Products::paginate(10)->through(function ($product) {
            $product->price = $product->price . ' $';
            return $product;
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $product = new Products();
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->image_url = $request->image_url;
        $product->admin_id = $request->admin_id;
        $product->category_id = $request->category_id;
        $product->created_at = now();
        $product->updated_at = now();
        $product->save();
        return response()->json(['message' => 'Product created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Products::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Products::find($id);
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->image_url = $request->image_url;
        $product->admin_id = $request->admin_id;
        $product->updated_at = now();
        $product->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Products::find($id);
        $product->delete();
    }
}

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
        try {
            return Products::find($id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product not found'], 404);
        }
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

    public function edit(Request $request, string $id){

            $product = Products::find($id);
            if(!$product){
                return response()->json(['message' => 'Product not found'], 404);
            }
            // if($product->admin_id !== $request->user()->id){
            //     return response()->json(['message' => 'You are not authorized to update this product'], 403);
            // }

            $updateData = $request->only(['title', 'description', 'price', 'quantity', 'image_url','admin_id','category_id']);
            $product->update($updateData);
            $product->save();

            return response()->json([
                'data' => $product,

                'message' => 'Product updated successfully'], 200);

    }
}

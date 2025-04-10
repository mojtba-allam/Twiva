<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;
class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $query = Products::approved();

        // Search by title
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort by price
        if ($request->has('sort_price')) {
            $direction = $request->sort_price === 'desc' ? 'desc' : 'asc';
            $query->orderBy('price', $direction);
        }

        // Sort by latest
        if ($request->has('sort_by') && $request->sort_by === 'latest') {
            $query->latest();
        }

        $products = $query->paginate(10);

        return response()->json([
            'message' => 'Products retrieved successfully',
            'total' => $products->total(),
            'page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'filters_applied' => [
                'search' => $request->search ?? null,
                'category' => $request->category ?? null,
                'min_price' => $request->min_price ?? null,
                'max_price' => $request->max_price ?? null,
                'sort_price' => $request->sort_price ?? null,
                'sort_by' => $request->sort_by ?? null
            ],
            'data' => ProductResource::collection($products)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'image_url' => 'nullable|url',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = new Products($validatedData);
        $product->business_account_id = Auth::guard('business')->user()->id;
        $product->status = Products::STATUS_PENDING;
        $product->save();

        return response()->json([
            'message' => 'Product created successfully and pending admin approval',
            'product' => new ProductResource($product),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Products::with(['category', 'businessAccount'])->findOrFail($id);

        if ($product->status !== Products::STATUS_APPROVED) {
            $user = null;
            $isAdmin = Auth::guard('admin')->check();
            $isBusiness = Auth::guard('business')->check();
            if (!$isAdmin && (!$isBusiness || Auth::guard('business')->user()->id !== $product->business_account_id)) {
                return response()->json(['message' => 'Product not found'], 404);
            }
        }

        return new ProductResource($product);
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


            $updateData = $request->only(['title', 'description', 'price', 'quantity', 'image_url','admin_id','category_id']);
            $product->update($updateData);
            $product->save();

            return response()->json([
                'data' => $product,

                'message' => 'Product updated successfully'], 200);

    }
}

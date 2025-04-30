<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\NotificationService;

class ProductController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $query = Products::where('status', 'approved')
            ->where('quantity', '>', 0);

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

        $user = Auth::guard('sanctum')->user();
        $isBusiness = $user && $user instanceof \App\Models\BusinessAccount;

        if (!$isBusiness) {
            return response()->json([
                'message' => 'Unauthorized. Only business accounts can create products.'
            ], 403);
        }

        $product = new Products($validatedData);
        $product->business_account_id = $user->id;
        $product->status = Products::STATUS_PENDING;
        $product->save();

        // Notify admins about new product
        $this->notificationService->notifyAdmins(
            'new_product',
            'New Product Added',
            "A new product '{$product->title}' has been added and needs approval",
            [
                'product_id' => $product->id,
                'business_account_id' => $product->business_account_id
            ]
        );

        return response()->json([
            'message' => 'Product created successfully and pending admin approval',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $product = Products::with(['category', 'businessAccount'])->findOrFail($id);

            if ($product->status !== Products::STATUS_APPROVED) {
                $user = null;
                $isAdmin = Auth::guard('admin')->check();
                $isBusiness = Auth::guard('business')->check();
                if (!$isAdmin && (!$isBusiness || Auth::guard('business')->user()->id !== $product->business_account_id)) {
                    return response()->json(['message' => 'Product not found'], 404);
                }
            }

            // Get the basic product resource
            $productResource = new ProductResource($product);
            $productData = $productResource->toArray(request());

            // Add additional fields for the detailed view
            $productData['description'] = $product->description;
            $productData['category_name'] = $product->category ? $product->category->name : null;
            $productData['business_name'] = $product->businessAccount ? $product->businessAccount->name : null;

            return response()->json($productData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
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
        try {
            $product = Products::findOrFail($id);

            // Check if the authenticated user is a business account and is the owner of the product
            $user = Auth::guard('sanctum')->user();
            $isBusiness = $user && $user instanceof \App\Models\BusinessAccount;

            if (!$isBusiness || $user->id !== $product->business_account_id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only edit your own products.'
                ], 403);
            }

            $updateData = $request->only(['title', 'description', 'price', 'quantity', 'image_url', 'category_id']);

            // If the product was previously approved or rejected, set it back to pending
            if ($product->status === Products::STATUS_APPROVED || $product->status === Products::STATUS_REJECTED) {
                $updateData['status'] = Products::STATUS_PENDING;
                $updateData['rejection_reason'] = null; // Clear any previous rejection reason

                // Notify admins about edited product
                $this->notificationService->notifyAdmins(
                    'product_edited',
                    'Product Edited',
                    "Product '{$product->title}' has been edited and needs approval",
                    [
                        'product_id' => $product->id,
                        'business_account_id' => $product->business_account_id
                    ]
                );
            }

            $product->update($updateData);
            $product->save();

            $message = $product->status === Products::STATUS_PENDING
                ? 'Product updated successfully and is pending admin approval'
                : 'Product updated successfully';

            return response()->json([
                'message' => $message
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }
}

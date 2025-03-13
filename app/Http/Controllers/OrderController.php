<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Products;
use App\Models\Categories;
use App\Models\Admin;
use App\Models\User;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;
        if (!$user) {
            return response()->json(['message' => 'Not Found'], 404);
        }
        if($user instanceof Admin){
        return Order::paginate(100)->through(function ($order) {
            // Decode the products_list JSON
            $products_list = json_decode($order->products_list, true);

            // Fetch all related products
            $products = Products::whereIn('id', collect($products_list)->pluck('product_id'))->get();

            // Add product URLs to the products_list
            $products_list_with_urls = collect($products_list)->map(function ($item) use ($products) {
                $product = $products->firstWhere('id', $item['product_id']);
                return [
                    'product_id' => $item['product_id'],
                    'product_title' => $product->title,
                    'quantity' => $item['quantity'],
                    'product_price' => $product->price .' $' ,
                    // 'product_category' => Categories::where('id', $product->category_id)->first()->name,
                    'product_image' => $product->image_url,
                    'product_url' => $product->product_url
                ];
            })->toArray();

            // Add the enhanced products_list to the order
            $order->products_list = $products_list_with_urls;

            $order->total_price = $order->total_price . ' $';

            return $order;
        });
        }elseif($user instanceof User){

            $orders = Order::where('user_id', $user_id)->get();
            if ($orders->isEmpty()) {
                return response()->json(['message' => 'No orders found for this user'], 404);
            }
            return $orders->map(function ($order) {
                // Decode the products_list JSON
                $products_list = json_decode($order->products_list, true);

                // Fetch all related products
                $products = Products::whereIn('id', collect($products_list)->pluck('product_id'))->get();

                // Add product URLs to the products_list
                $products_list_with_urls = collect($products_list)->map(function ($item) use ($products) {
                    $product = $products->firstWhere('id', $item['product_id']);
                    return [
                        'product_id' => $item['product_id'],
                        'product_title' => $product->title,
                        'quantity' => $item['quantity'],
                        'product_price' => $product->price .' $',
                        'product_category' => optional(Categories::where('id', $product->category_id)->first())->name ?? 'Unknown Category',
                        'product_image' => $product->image_url,
                        'product_url' => $product->product_url
                    ];
                })->toArray();

                // Add the enhanced products_list to the order
                $order->products_list = $products_list_with_urls;

                return $order;
            });

        }
        else{
            return response()->json(['message' => 'You are not authorized to view this page'], 403);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $user_id = Auth::guard('user')->id();

        // Get the products list from request
        $products_list = $request->products_list;

        // Get all product IDs from the request
        // $productIds = collect($products_list)->pluck('product_id');

        // Fetch all products from database
        $products = Products::whereIn('id', collect($products_list)->pluck('product_id'))->get();

        // Calculate total price
        $total_price = $products->sum(function ($product) use ($products_list) {
            $quantity = collect($products_list)
                ->firstWhere('product_id', $product->id)['quantity'];
            return $product->price * $quantity;
        });

        // Calculate total quantity
        $total_quantity = collect($products_list)->sum('quantity');

        $order = new Order();
        $order->user_id = $user_id;
        $order->products_list = json_encode($products_list); // Encode the array to JSON
        $order->total_quantity = $total_quantity;
        $order->total_price = $total_price;
        $order->status = $request->status;
        $order->created_at = now();
        $order->updated_at = now();
        $order->save();

        return response()->json(['message' => 'Order created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $order = Order::find($id);
        return $order;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::find($id);
        $order->user_id = $request->user_id;
        $order->product_id = $request->product_id;
        $order->quantity = $request->quantity;
        $order->total_price = $request->total_price;
        $order->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::find($id);
        $order->delete();
    }
}

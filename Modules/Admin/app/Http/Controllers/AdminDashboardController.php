<?php

namespace Modules\Admin\app\Http\Controllers;

use Modules\Admin\app\Http\Controllers\Controller;
use Modules\User\app\Models\User;
use Modules\Order\app\Models\Order;
use Modules\Product\app\Models\Product;
use Modules\Business\app\Models\Business;
use Modules\Category\app\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class AdminDashboardController extends Controller
{
    public function index()
    {
        // Overview Statistics
        $statistics = [
            'users' => [
                'total' => User::count(),
                'new_today' => User::whereDate('created_at', Carbon::today())->count()
            ],
            'businesses' => [
                'total' => Business::count(),
                'new_today' => Business::whereDate('created_at', Carbon::today())->count()
            ],
            'products' => [
                'total' => Product::count(),
                'pending' => Product::where('status', 'pending')->count(),
                'approved' => Product::where('status', 'approved')->count(),
                'rejected' => Product::where('status', 'rejected')->count()
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count()
            ],
            'revenue' => [
                'total' => Order::where('status', 'completed')->sum('total_price'),
                'today' => Order::where('status', 'completed')
                    ->whereDate('created_at', Carbon::today())
                    ->sum('total_price')
            ]
        ];

        // Recent Activities
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                // Create a new array with selected fields
                $orderData = $order->toArray();
                // Remove products_list
                unset($orderData['products_list']);
                // Remove user_id
                unset($orderData['user_id']);
                // Add order_url
                $orderData['order_url'] = url("/api/v1/orders/{$order->id}");

                // Simplify user data to only include id and name
                if (isset($orderData)) {
                    $orderData['user'] = [
                        'user_id' => $order->user->id,
                        'user_name' => $order->user->name,
                        'user_url' => url("/api/v1/users/{$order->user->id}")
                    ];
                }

                return $orderData;
            });

        $recentProducts = Product::with('business')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($product) {
                // Create a new array with selected fields only
                $productData = [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => $product->price,
                    'image_url' => $product->image_url,
                    'product_url' => $product->product_url,
                    'category_id' => $product->category_id,
                    'quantity' => $product->quantity,
                    'status' => $product->status,
                    'rejection_reason' => $product->rejection_reason,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];

                // Simplify business data
                if ($product->business) {
                    $productData['business'] = [
                        'id' => $product->business->id,
                        'name' => $product->business->name,
                        'profile_picture' => $product->business->profile_picture,
                        'business_url' => $product->business->business_url
                    ];
                }

                return $productData;
            });

        $recentActivities = [
            'orders' => $recentOrders,
            'products' => $recentProducts,
            'users' => User::latest()
                ->take(5)
                ->get(),
            'businesses' => Business::latest()
                ->take(5)
                ->get()
        ];

        // Product Statistics by Category
        $productsByCategory = Category::withCount(['Product as total_products',
            'Product as approved_products' => function ($query) {
                $query->where('status', 'approved');
            },
            'Product as pending_products' => function ($query) {
                $query->where('status', 'pending');
            }
        ])->get();

        // Sales Analytics
        $salesAnalytics = [
            'daily' => $this->getDailySales(),
            'monthly' => $this->getMonthlySales(),
            'yearly' => $this->getYearlySales()
        ];

        return response()->json([
            'statistics' => $statistics,
            'recent_activities' => $recentActivities,
            'products_by_category' => $productsByCategory,
            'sales_analytics' => $salesAnalytics
        ]);
    }

    private function getDailySales()
    {
        return Order::where('status', 'completed')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getMonthlySales()
    {
        return Order::where('status', 'completed')
            ->whereDate('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    private function getYearlySales()
    {
        return Order::where('status', 'completed')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get();
    }
}
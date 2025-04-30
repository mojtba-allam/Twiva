<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Products;
use App\Models\BusinessAccount;
use App\Models\Categories;
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
                'total' => BusinessAccount::count(),
                'new_today' => BusinessAccount::whereDate('created_at', Carbon::today())->count()
            ],
            'products' => [
                'total' => Products::count(),
                'pending' => Products::where('status', 'pending')->count(),
                'approved' => Products::where('status', 'approved')->count(),
                'rejected' => Products::where('status', 'rejected')->count()
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
        $recentActivities = [
            'orders' => Order::with('user')
                ->latest()
                ->take(5)
                ->get(),
            'products' => Products::with('businessAccount')
                ->latest()
                ->take(5)
                ->get(),
            'users' => User::latest()
                ->take(5)
                ->get(),
            'businesses' => BusinessAccount::latest()
                ->take(5)
                ->get()
        ];

        // Product Statistics by Category
        $productsByCategory = Categories::withCount(['products as total_products',
            'products as approved_products' => function ($query) {
                $query->where('status', 'approved');
            },
            'products as pending_products' => function ($query) {
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
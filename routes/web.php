<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\BusinessAccountController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\NotificationController;

// Public routes
// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

// // User Authentication Routes
// Route::get('/register', function () {
//     return view('auth.register');
// })->name('register');
// Route::post('/register', [AuthController::class, 'register']);

// Route::get('/login', function () {
//     return view('auth.login');
// })->name('login');
// Route::post('/login', [AuthController::class, 'login']);

// // Admin Routes
// Route::prefix('admin')->group(function () {
//     // Admin Auth
//     Route::get('/login', function () {
//         return view('admin.login');
//     })->name('admin.login');
//     Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

//     // Protected Admin Routes
//     Route::middleware(['auth'])->group(function () {
//         Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
//         Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');

//         // Admin Product Management
//         Route::prefix('products')->group(function () {
//             Route::get('/', [AdminProductController::class, 'index'])->name('admin.products.index');
//             Route::get('/pending', [AdminProductController::class, 'pendingProducts'])->name('admin.products.pending');
//             Route::get('/rejected', [AdminProductController::class, 'rejectedProducts'])->name('admin.products.rejected');
//             Route::get('/{id}/edit', [AdminProductController::class, 'edit'])->name('admin.products.edit');
//             Route::post('/{id}/approve', [AdminProductController::class, 'approveProduct'])->name('admin.products.approve');
//             Route::post('/{id}/reject', [AdminProductController::class, 'rejectProduct'])->name('admin.products.reject');
//         });

//         // Admin Category Management
//         Route::prefix('categories')->group(function () {
//             Route::get('/', [CategoriesController::class, 'adminIndex'])->name('admin.categories.index');
//             Route::get('/create', [CategoriesController::class, 'create'])->name('admin.categories.create');
//             Route::post('/store', [CategoriesController::class, 'store'])->name('admin.categories.store');
//             Route::get('/{id}/edit', [CategoriesController::class, 'edit'])->name('admin.categories.edit');
//             Route::put('/{id}', [CategoriesController::class, 'update'])->name('admin.categories.update');
//             Route::delete('/{id}', [CategoriesController::class, 'destroy'])->name('admin.categories.destroy');
//         });

//         // Admin User Management
//         Route::prefix('users')->group(function () {
//             Route::get('/', [UserController::class, 'adminIndex'])->name('admin.users.index');
//             Route::get('/{id}', [UserController::class, 'show'])->name('admin.users.show');
//         });

//         // Admin Order Management
//         Route::prefix('orders')->group(function () {
//             Route::get('/', [OrderController::class, 'adminIndex'])->name('admin.orders.index');
//             Route::get('/{id}', [OrderController::class, 'show'])->name('admin.orders.show');
//             Route::put('/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');
//         });
//     });
// });

// // Business Account Routes
// Route::prefix('business')->group(function () {
//     Route::get('/register', function () {
//         return view('business.register');
//     })->name('business.register');
//     Route::post('/register', [BusinessAccountController::class, 'register']);

//     Route::get('/login', function () {
//         return view('business.login');
//     })->name('business.login');
//     Route::post('/login', [BusinessAccountController::class, 'login']);

//     // Protected Business Routes
//     Route::middleware(['auth'])->group(function () {
//         Route::get('/dashboard', [BusinessAccountController::class, 'dashboard'])->name('business.dashboard');
//         Route::get('/profile', [BusinessAccountController::class, 'profile'])->name('business.profile');
//         Route::get('/profile/edit', [BusinessAccountController::class, 'editProfile'])->name('business.profile.edit');
//         Route::put('/profile/update', [BusinessAccountController::class, 'updateProfile'])->name('business.profile.update');

//         // Business Product Management
//         Route::prefix('products')->group(function () {
//             Route::get('/', [ProductController::class, 'businessIndex'])->name('business.products.index');
//             Route::get('/create', [ProductController::class, 'create'])->name('business.products.create');
//             Route::post('/store', [ProductController::class, 'store'])->name('business.products.store');
//             Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('business.products.edit');
//             Route::put('/{id}', [ProductController::class, 'update'])->name('business.products.update');
//             Route::delete('/{id}', [ProductController::class, 'destroy'])->name('business.products.destroy');
//         });
//     });
// });

// // Public Product & Category Routes
// Route::get('/products', [ProductController::class, 'index'])->name('products.index');
// Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
// Route::get('/categories', [CategoriesController::class, 'index'])->name('categories.index');
// Route::get('/categories/{id}', [CategoriesController::class, 'show'])->name('categories.show');

// // Protected User Routes
// Route::middleware(['auth'])->group(function () {
//     Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
//     Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('user.profile.edit');
//     Route::put('/profile/update', [UserController::class, 'updateProfile'])->name('user.profile.update');

//     // User Orders
//     Route::prefix('orders')->group(function () {
//         Route::get('/', [OrderController::class, 'userIndex'])->name('user.orders.index');
//         Route::get('/create', [OrderController::class, 'create'])->name('user.orders.create');
//         Route::post('/store', [OrderController::class, 'store'])->name('user.orders.store');
//         Route::get('/{id}', [OrderController::class, 'show'])->name('user.orders.show');
//     });

//     // Notifications
//     Route::prefix('notifications')->group(function () {
//         Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
//         Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
//         Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
//     });
// });

// // Logout Routes
// Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
// Route::post('/business/logout', [BusinessAccountController::class, 'logout'])->name('business.logout');


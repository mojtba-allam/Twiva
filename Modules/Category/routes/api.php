<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\app\Http\Controllers\CategoryController;


Route::apiResource('category', CategoryController::class)->names('category');
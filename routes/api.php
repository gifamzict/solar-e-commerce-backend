<?php
// In routes/api.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Public (unprotected) routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');

// Category routes
Route::apiResource('categories', CategoryController::class);

// Product routes
Route::apiResource('products', ProductController::class);

// ... (Your protected routes will go here)

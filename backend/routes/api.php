<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\SaleController;




Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('sales', SaleController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('purchases', PurchaseController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('sales',SaleController::class);
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::apiResource('purchases', PurchaseController::class)
        ->middleware('role:admin,manager,staff');

    Route::apiResource('sales', SaleController::class)
        ->middleware('role:admin,manager,staff');

    Route::apiResource('products', ProductController::class)
        ->middleware('role:admin,manager');

    Route::apiResource('users', UserController::class)
        ->middleware('role:admin');
});



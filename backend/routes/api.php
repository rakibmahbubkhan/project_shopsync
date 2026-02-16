<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\SaleController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\AuditLogController;




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

Route::middleware('auth:sanctum')
    ->get('/reports/dashboard', [ReportController::class, 'dashboard']);

    Route::get('/reports/sales', [ReportController::class, 'salesReport']);

    Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
    Route::get('/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/reports/purchases', [ReportController::class, 'purchaseReport']);
    Route::get('/reports/inventory-valuation', [ReportController::class, 'inventoryValuation']);
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock']);

});

Route::middleware(['auth:sanctum', 'permission:manage_products'])
    ->apiResource('products', ProductController::class);

Route::middleware(['auth:sanctum', 'permission:view_reports'])
    ->get('/reports/dashboard', [ReportController::class, 'dashboard']);

    Route::middleware(['auth:sanctum', 'permission:manage_users'])
    ->get('/audit-logs', [AuditLogController::class, 'index']);







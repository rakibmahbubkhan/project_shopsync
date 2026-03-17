 <?php

// use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\API\ProductController;
// use App\Http\Controllers\API\CategoryController;
// use App\Http\Controllers\API\SupplierController;
// use App\Http\Controllers\API\PurchaseController;
// use App\Http\Controllers\API\SaleController;
// use App\Http\Controllers\API\UserController;
// use App\Http\Controllers\API\ReportController;
// use App\Http\Controllers\API\AuditLogController;
// use App\Http\Controllers\API\FinancialReportController;
//     use App\Http\Controllers\API\AuthController;



//     Route::middleware('auth:sanctum')->group(function () {
//         Route::apiResource('products', ProductController::class);
//         Route::apiResource('categories', CategoryController::class);
//         Route::apiResource('suppliers', SupplierController::class);
//         Route::apiResource('purchases', PurchaseController::class);
//         Route::apiResource('sales', SaleController::class);
//     });

//     Route::middleware('auth:sanctum')->group(function () {
//         Route::apiResource('purchases', PurchaseController::class);
//     });

//     Route::middleware('auth:sanctum')->group(function () {
//         Route::apiResource('sales',SaleController::class);
//     });

//     Route::middleware(['auth:sanctum'])->group(function () {

//     Route::apiResource('purchases', PurchaseController::class)
//     ->middleware('role:admin,manager,staff');

//     Route::apiResource('sales', SaleController::class)
//     ->middleware('role:admin,manager,staff');

//     Route::apiResource('products', ProductController::class)
//     ->middleware('role:admin,manager');

//     Route::apiResource('users', UserController::class)
//     ->middleware('role:admin');
//     });

//     Route::middleware('auth:sanctum')
//     ->get('/reports/dashboard', [ReportController::class, 'dashboard']);

//     Route::get('/reports/sales', [ReportController::class, 'salesReport']);

//     Route::middleware(['auth:sanctum'])->group(function () {
//         Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
//         Route::get('/reports/sales', [ReportController::class, 'salesReport']);
//         Route::get('/reports/purchases', [ReportController::class, 'purchaseReport']);
//         Route::get('/reports/inventory-valuation', [ReportController::class, 'inventoryValuation']);
//         Route::get('/reports/low-stock', [ReportController::class, 'lowStock']);
//     });

//     Route::middleware(['auth:sanctum', 'permission:manage_products'])
//     ->apiResource('products', ProductController::class);

//     Route::middleware(['auth:sanctum', 'permission:view_reports'])
//     ->get('/reports/dashboard', [ReportController::class, 'dashboard']);

//     Route::middleware(['auth:sanctum', 'permission:manage_users'])
//     ->get('/audit-logs', [AuditLogController::class, 'index']);


//     Route::middleware(['auth:sanctum', 'permission:view_reports'])
//     ->get('/financial/trial-balance', [FinancialReportController::class, 'trialBalance']);

//     Route::get('/financial/profit-loss', [FinancialReportController::class, 'profitLoss']);
//     Route::get('/financial/balance-sheet', [FinancialReportController::class, 'balanceSheet']);

//     Route::middleware('auth:sanctum')
//     ->get('/sales/{sale}/receipt', [SaleController::class, 'receipt']);

//     Route::get('/returns/{return}/receipt', [SaleController::class, 'returnReceipt']);


//     Route::post('/returns/{return}/approve', [SaleController::class, 'approve']);

//     Route::middleware(['auth:sanctum'])->group(function () {
//         Route::post('/returns/{return}/approve', [SaleController::class, 'approve']);
//     });


// Route::post('/login', [AuthController::class, 'login']);
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);
//     Route::get('/user', [AuthController::class, 'user']);
    
  
// });


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\SaleController;
use App\Http\Controllers\API\StockTransferController;
use App\Http\Controllers\API\StockAdjustmentController;
use App\Http\Controllers\API\FinancialReportController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\ReturnController;
use App\Http\Controllers\API\AuditLogController;

// Public Auth Routes
Route::post('/login', [AuthController::class, 'login']);

// Protected Business Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // 1. User Management
    Route::get('/users/roles', [UserController::class, 'getRoles']);
    Route::apiResource('users', UserController::class);

    // 2. Inventory & Products
    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);

    // 3. Sales & POS
    Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt']);
    Route::apiResource('sales', SaleController::class);

    // 4. Purchases & Suppliers
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('purchases', PurchaseController::class);

    // 5. Stock Operations
    Route::apiResource('stock-transfers', StockTransferController::class);
    Route::post('stock-adjustments', [StockAdjustmentController::class, 'store']);

    // 6. Returns & Refunds
    Route::post('returns', [ReturnController::class, 'store']);

    // 7. Accounting & Financial Reports
    Route::get('reports/trial-balance', [FinancialReportController::class, 'trialBalance']);
    Route::get('reports/profit-loss', [FinancialReportController::class, 'profitAndLoss']);

    // 8. Dashboard & Analytics
    Route::get('reports/summary', [ReportController::class, 'getSummary']);
    Route::get('reports/sales-trend', [ReportController::class, 'getSalesTrend']);
    Route::get('reports/low-stock', [ReportController::class, 'getLowStockReport']);

    // 9. System Auditing
    Route::get('audit-logs', [AuditLogController::class, 'index']);
});




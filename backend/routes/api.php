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
use App\Http\Controllers\API\PurchaseReturnController;
use App\Http\Controllers\API\AuditLogController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\WarehouseController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\UnitController;
use App\Http\Controllers\API\TaxController;
use App\Http\Controllers\API\VariantController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ExpenseCategoryController;
use App\Http\Controllers\API\ExpenseController;

// Dummy login route to prevent redirect exceptions for API auth
Route::any('/login', function () {
    return response()->json(['message' => 'Unauthenticated'], 401);
})->name('login');

// Public Auth Route
Route::post('/login', [AuthController::class, 'login']);

// Protected Business Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // User Management
    Route::get('/users/roles', [UserController::class, 'getRoles']);
    Route::apiResource('users', UserController::class);

    // Inventory & Products
    Route::get('/products/form-data', [ProductController::class, 'getFormData']);
    Route::get('products/create', [ProductController::class, 'create']);
    Route::apiResource('brands', BrandController::class)->only(['index']);
    Route::apiResource('categories', CategoryController::class)->only(['index']);
    Route::get('units', [UnitController::class, 'index']);

    // Damaged Products CRUD – must come before the generic product resource
    Route::get('/products/damaged', [ProductController::class, 'damagedProducts']);
    Route::post('/products/damaged', [ProductController::class, 'storeDamaged']);
    Route::put('/products/damaged/{id}', [ProductController::class, 'updateDamaged']);
    Route::delete('/products/damaged/{id}', [ProductController::class, 'destroyDamaged']);

    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);

    // Tax Management
    Route::apiResource('taxes', TaxController::class);

    // Variant Management
    Route::apiResource('variants', VariantController::class);
    Route::prefix('variants/{variant}')->group(function () {
        Route::post('/items', [VariantController::class, 'storeItem']);
        Route::put('/items/{item}', [VariantController::class, 'updateItem']);
        Route::delete('/items/{item}', [VariantController::class, 'destroyItem']);
    });

    // Sales & POS
    Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt']);
    Route::apiResource('sales', SaleController::class);
    Route::get('/sales/recent-products', [SaleController::class, 'recentProducts']);

    // Purchases & Suppliers
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('purchases', PurchaseController::class);
    Route::prefix('purchase-returns')->group(function () {
        Route::get('/', [PurchaseReturnController::class, 'index']);
        Route::get('/search-purchases', [PurchaseReturnController::class, 'searchPurchases']);
        Route::get('/stats', [PurchaseReturnController::class, 'stats']);
        Route::post('/', [PurchaseReturnController::class, 'store']);
        Route::get('/{id}', [PurchaseReturnController::class, 'show']);
        Route::post('/{id}/approve', [PurchaseReturnController::class, 'approve']);
        Route::post('/{id}/reject', [PurchaseReturnController::class, 'reject']);
    });
    Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive']);
    Route::post('purchases/{purchase}/payments', [PurchaseController::class, 'addPayment']);
    Route::get('purchases/{purchase}/items', [PurchaseController::class, 'getItems']);

    // Stock Transfers
    Route::get('/stock-transfers/warehouses', [StockTransferController::class, 'getWarehouses']);
    Route::get('/stock-transfers', [StockTransferController::class, 'index']);
    Route::get('/stock-transfers/available-products', [StockTransferController::class, 'getAvailableProducts']);
    Route::post('/stock-transfers', [StockTransferController::class, 'store']);
    Route::get('/stock-transfers/{stockTransfer}', [StockTransferController::class, 'show']);
    Route::post('/stock-transfers/{stockTransfer}/cancel', [StockTransferController::class, 'cancel']);
    Route::delete('/stock-transfers/{stockTransfer}', [StockTransferController::class, 'destroy']);
    Route::get('/stock-transfers/report', [StockTransferController::class, 'report']);

    // Returns & Refunds
    Route::prefix('returns')->group(function () {
        Route::get('/', [ReturnController::class, 'index']);
        Route::get('/search-sales', [ReturnController::class, 'searchSales']);
        Route::get('/stats', [ReturnController::class, 'stats']);
        Route::post('/', [ReturnController::class, 'store']);
        Route::get('/{id}', [ReturnController::class, 'show']);
        Route::post('/{id}/approve', [ReturnController::class, 'approve']);
        Route::post('/{id}/reject', [ReturnController::class, 'reject']);
    });

    // Financial Reports
    Route::get('reports/trial-balance', [FinancialReportController::class, 'trialBalance']);
    Route::get('reports/profit-loss', [FinancialReportController::class, 'profitAndLoss']);

    // Dashboard & Analytics
    Route::get('reports/summary', [ReportController::class, 'getSummary']);
    Route::get('reports/sales-trend', [ReportController::class, 'getSalesTrend']);
    Route::get('reports/low-stock', [ReportController::class, 'getLowStockReport']);

    // System Auditing
    Route::get('audit-logs', [AuditLogController::class, 'index']);

    // Customers
    Route::prefix('customers')->group(function () {
        Route::get('pending-payments', [CustomerController::class, 'pendingPayments']);
        Route::get('pending-payments/count', [CustomerController::class, 'pendingPaymentsCount']);
    });
    Route::apiResource('customers', CustomerController::class);
    Route::patch('customers/{id}/status', [CustomerController::class, 'updateStatus']);

    // Payments
    Route::prefix('payments')->group(function () {
        Route::post('/', [PaymentController::class, 'store']);
        Route::post('/bulk', [PaymentController::class, 'bulkStore']);
        Route::get('/history/{saleId}', [PaymentController::class, 'history']);
    });

    // Warehouses
    Route::get('warehouses/dropdown', [WarehouseController::class, 'getDropdown']);
    Route::apiResource('warehouses', WarehouseController::class);


    // Expenses Module Routes
    Route::apiResource('expense-categories', ExpenseCategoryController::class);
    Route::apiResource('expenses', ExpenseController::class);

    // POS Init Data
    Route::get('/pos/init', function () {
        return response()->json([
            'categories' => \App\Models\Category::select('id', 'name')->get(),
            'brands' => \App\Models\Brand::select('id', 'name')->get(),
            'customers' => \App\Models\Customer::select('id', 'name')->get(),
            'warehouses' => \App\Models\Warehouse::select('id', 'name')->get(),
            'taxes' => \App\Models\Tax::select('id', 'name', 'percentage')->get(),
            'variants' => \App\Models\Variant::with('items')->get(),
        ]);
    });
});

// Debug route (keep at bottom)
Route::get('/debug-sale', function () {
    try {
        $sale = new \App\Models\Sale();
        return response()->json(['message' => 'Sale model works']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
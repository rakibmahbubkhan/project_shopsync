<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get comprehensive dashboard statistics.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard(Request $request)
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $yearStart = now()->startOfYear()->toDateString();

        // Core metrics
        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        
        // Low stock count (using either stock_quantity or stock column)
        $lowStockCount = Product::where(function($query) {
            $query->whereColumn('stock_quantity', '<=', 'alert_quantity')
                  ->orWhereColumn('stock', '<=', 'alert_quantity');
        })->count();

        // Sales metrics
        $todaySales = Sale::whereDate('sale_date', $today)->sum('total_amount');
        $weekSales = Sale::whereDate('sale_date', '>=', $weekStart)->sum('total_amount');
        $monthSales = Sale::whereDate('sale_date', '>=', $monthStart)->sum('total_amount');
        $yearSales = Sale::whereDate('sale_date', '>=', $yearStart)->sum('total_amount');
        
        // Profit metrics
        $todayProfit = Sale::whereDate('sale_date', $today)->sum('gross_profit');
        $monthProfit = Sale::whereDate('sale_date', '>=', $monthStart)->sum('gross_profit');
        
        // Purchase metrics
        $todayPurchases = Purchase::whereDate('purchase_date', $today)->sum('total_amount');
        $monthPurchases = Purchase::whereDate('purchase_date', '>=', $monthStart)->sum('total_amount');

        // Top selling products
        $topProducts = SaleItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->with('product:id,name,sku')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_products' => $totalProducts,
                    'total_customers' => $totalCustomers,
                    'low_stock_count' => $lowStockCount,
                ],
                'sales' => [
                    'today' => [
                        'amount' => (float) $todaySales,
                        'profit' => (float) $todayProfit,
                    ],
                    'week' => (float) $weekSales,
                    'month' => [
                        'amount' => (float) $monthSales,
                        'profit' => (float) $monthProfit,
                    ],
                    'year' => (float) $yearSales,
                ],
                'purchases' => [
                    'today' => (float) $todayPurchases,
                    'month' => (float) $monthPurchases,
                ],
                'top_products' => $topProducts,
            ],
            'message' => 'Dashboard data retrieved successfully'
        ]);
    }

    /**
     * Get sales report with detailed metrics.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesReport(Request $request)
    {
        $query = Sale::with(['customer', 'user', 'items.product'])
            ->withCount('items');

        // Apply date filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('sale_date', [
                $request->start_date,
                $request->end_date
            ]);
        } elseif ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('sale_date', now()->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('sale_date', [
                        now()->startOfWeek()->toDateString(),
                        now()->endOfWeek()->toDateString()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('sale_date', now()->month)
                          ->whereYear('sale_date', now()->year);
                    break;
                case 'quarter':
                    $query->whereBetween('sale_date', [
                        now()->startOfQuarter()->toDateString(),
                        now()->endOfQuarter()->toDateString()
                    ]);
                    break;
                case 'year':
                    $query->whereYear('sale_date', now()->year);
                    break;
            }
        }

        // Apply payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Apply customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();

        // Calculate summary metrics
        $totalSales = $sales->sum('total_amount');
        $totalProfit = $sales->sum('gross_profit');
        $totalDiscount = $sales->sum('discount');
        $totalTax = $sales->sum('tax');
        $averageOrderValue = $sales->count() > 0 ? $totalSales / $sales->count() : 0;

        // Payment status breakdown
        $paymentBreakdown = [
            'paid' => $sales->where('payment_status', 'paid')->count(),
            'partial' => $sales->where('payment_status', 'partial')->count(),
            'unpaid' => $sales->where('payment_status', 'unpaid')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_sales' => (float) $totalSales,
                    'total_profit' => (float) $totalProfit,
                    'total_discount' => (float) $totalDiscount,
                    'total_tax' => (float) $totalTax,
                    'average_order_value' => (float) $averageOrderValue,
                    'total_transactions' => $sales->count(),
                    'total_items_sold' => $sales->sum('items_count'),
                ],
                'payment_breakdown' => $paymentBreakdown,
                'sales' => $sales,
            ],
            'message' => 'Sales report generated successfully'
        ]);
    }

    /**
     * Get purchase report with detailed metrics.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseReport(Request $request)
    {
        $query = Purchase::with(['supplier', 'user', 'items.product'])
            ->withCount('items');

        // Apply date filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('purchase_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Apply supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->orderBy('purchase_date', 'desc')->get();

        // Calculate summary metrics
        $totalPurchases = $purchases->sum('total_amount');
        $totalPaid = $purchases->sum('paid_amount');
        $totalDue = $totalPurchases - $totalPaid;

        // Status breakdown
        $statusBreakdown = [
            'ordered' => $purchases->where('status', 'ordered')->count(),
            'received' => $purchases->where('status', 'received')->count(),
            'pending' => $purchases->where('status', 'pending')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_purchases' => (float) $totalPurchases,
                    'total_paid' => (float) $totalPaid,
                    'total_due' => (float) $totalDue,
                    'total_transactions' => $purchases->count(),
                    'total_items' => $purchases->sum('items_count'),
                ],
                'status_breakdown' => $statusBreakdown,
                'purchases' => $purchases,
            ],
            'message' => 'Purchase report generated successfully'
        ]);
    }

    /**
     * Get sales trend data for charts.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalesTrend(Request $request)
    {
        $period = $request->period ?? 'week'; // week, month, year
        $trend = [];

        switch ($period) {
            case 'week':
                $days = 7;
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $revenue = Sale::whereDate('sale_date', $date->format('Y-m-d'))
                        ->sum('total_amount');
                    $profit = Sale::whereDate('sale_date', $date->format('Y-m-d'))
                        ->sum('gross_profit');
                    
                    $trend[] = [
                        'date' => $date->format('D'),
                        'full_date' => $date->format('Y-m-d'),
                        'revenue' => (float) $revenue,
                        'profit' => (float) $profit,
                    ];
                }
                break;

            case 'month':
                for ($i = 1; $i <= now()->daysInMonth; $i++) {
                    $date = now()->setDay($i);
                    $revenue = Sale::whereDate('sale_date', $date->format('Y-m-d'))
                        ->sum('total_amount');
                    
                    $trend[] = [
                        'date' => $date->format('M d'),
                        'full_date' => $date->format('Y-m-d'),
                        'revenue' => (float) $revenue,
                    ];
                }
                break;

            case 'year':
                for ($i = 1; $i <= 12; $i++) {
                    $date = now()->setMonth($i);
                    $revenue = Sale::whereMonth('sale_date', $i)
                        ->whereYear('sale_date', now()->year)
                        ->sum('total_amount');
                    
                    $trend[] = [
                        'month' => $date->format('M'),
                        'month_num' => $i,
                        'revenue' => (float) $revenue,
                    ];
                }
                break;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'trend' => $trend,
            ],
            'message' => 'Sales trend retrieved successfully'
        ]);
    }

    /**
     * Get inventory valuation report.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function inventoryValuation()
    {
        $products = Product::select(
                'id',
                'name',
                'sku',
                'stock_quantity',
                'cost_price',
                'selling_price',
                'alert_quantity'
            )
            ->where('stock_quantity', '>', 0)
            ->get();

        $totalValue = $products->sum(function ($product) {
            return $product->stock_quantity * $product->cost_price;
        });

        $totalRetailValue = $products->sum(function ($product) {
            return $product->stock_quantity * $product->selling_price;
        });

        $potentialProfit = $totalRetailValue - $totalValue;

        // Category breakdown
        $categoryBreakdown = Product::select(
                'categories.name as category_name',
                DB::raw('SUM(products.stock_quantity) as total_quantity'),
                DB::raw('SUM(products.stock_quantity * products.cost_price) as total_value')
            )
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_products' => $products->count(),
                    'total_value' => (float) $totalValue,
                    'total_retail_value' => (float) $totalRetailValue,
                    'potential_profit' => (float) $potentialProfit,
                    'profit_margin' => $totalRetailValue > 0 
                        ? round(($potentialProfit / $totalRetailValue) * 100, 2) 
                        : 0,
                ],
                'category_breakdown' => $categoryBreakdown,
                'products' => $products,
            ],
            'message' => 'Inventory valuation retrieved successfully'
        ]);
    }

    /**
     * Get low stock products report.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function lowStock()
    {
        $products = Product::with(['category', 'unit'])
            ->where(function($query) {
                $query->whereColumn('stock_quantity', '<=', 'alert_quantity')
                      ->orWhereColumn('stock', '<=', 'alert_quantity');
            })
            ->orderByRaw('CAST(stock_quantity AS DECIMAL) ASC')
            ->get()
            ->map(function ($product) {
                $stock = $product->stock_quantity ?? $product->stock ?? 0;
                $alert = $product->alert_quantity ?? 5;
                $status = $stock <= 0 ? 'out_of_stock' : ($stock <= $alert ? 'low' : 'normal');
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category->name ?? 'N/A',
                    'unit' => $product->unit->name ?? 'pcs',
                    'current_stock' => (float) $stock,
                    'alert_quantity' => (float) $alert,
                    'status' => $status,
                    'cost_price' => (float) $product->cost_price,
                    'selling_price' => (float) $product->selling_price,
                    'reorder_amount' => max($alert * 2 - $stock, 0),
                ];
            });

        $summary = [
            'total_low_stock' => $products->where('status', 'low')->count(),
            'total_out_of_stock' => $products->where('status', 'out_of_stock')->count(),
            'products_needing_reorder' => $products->whereIn('status', ['low', 'out_of_stock'])->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'products' => $products,
            ],
            'message' => 'Low stock report retrieved successfully'
        ]);
    }

    /**
     * Get profit & loss report.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        // Revenue from sales
        $revenue = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(total_amount) as total'),
                DB::raw('SUM(gross_profit) as gross_profit'),
                DB::raw('SUM(discount) as total_discount'),
                DB::raw('SUM(tax) as total_tax')
            )
            ->first();

        // Cost of Goods Sold (COGS)
        $cogs = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->sum(DB::raw('sale_items.quantity * sale_items.purchase_price'));

        // Purchase totals
        $purchases = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('total_amount');

        // Calculate expenses (if you have an expenses table)
        // $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'revenue' => [
                    'total' => (float) ($revenue->total ?? 0),
                    'gross_profit' => (float) ($revenue->gross_profit ?? 0),
                    'discounts' => (float) ($revenue->total_discount ?? 0),
                    'taxes' => (float) ($revenue->total_tax ?? 0),
                ],
                'costs' => [
                    'cogs' => (float) $cogs,
                    'purchases' => (float) $purchases,
                ],
                'summary' => [
                    'net_revenue' => (float) (($revenue->total ?? 0) - ($revenue->total_tax ?? 0)),
                    'gross_margin' => (float) (($revenue->total ?? 0) - $cogs),
                    'net_profit' => (float) (($revenue->total ?? 0) - $cogs),
                ]
            ],
            'message' => 'Profit & loss report generated successfully'
        ]);
    }

    /**
     * Get summary statistics for dashboard cards.
     * (Compatibility method)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummary()
    {
        $today = now()->toDateString();

        return response()->json([
            'total_revenue' => (float) Sale::sum('total_amount'),
            'total_profit' => (float) Sale::sum('gross_profit'),
            'total_purchases' => (float) Purchase::sum('total_amount'),
            'low_stock_count' => Product::where(function($query) {
                $query->whereColumn('stock_quantity', '<=', 'alert_quantity')
                      ->orWhereColumn('stock', '<=', 'alert_quantity');
            })->count(),
            'active_customers' => Customer::count(),
            'today_sales' => (float) Sale::whereDate('sale_date', $today)->sum('total_amount'),
        ]);
    }

    /**
     * Get low stock report (alias for compatibility).
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLowStockReport()
    {
        return $this->lowStock();
    }
}
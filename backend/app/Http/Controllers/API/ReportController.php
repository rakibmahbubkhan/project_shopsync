<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    //

    public function dashboard()
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();

        return response()->json([
            'total_products' => Product::count(),

            'low_stock_products' => Product::whereColumn('stock_quantity', '<=', 'alert_quantity')->count(),

            'today_sales' => Sale::whereDate('sale_date', $today)
                ->sum('total_amount'),

            'monthly_sales' => Sale::whereDate('sale_date', '>=', $monthStart)
                ->sum('total_amount'),

            'today_purchases' => Purchase::whereDate('purchase_date', $today)
                ->sum('total_amount'),
        ]);
    }

    public function salesReport(Request $request)
    {
        $query = Sale::with('customer');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('sale_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $sales = $query->get();

        return response()->json([
            'total_sales' => $sales->sum('total_amount'),
            'total_discount' => $sales->sum('discount'),
            'total_tax' => $sales->sum('tax'),
            'data' => $sales
        ]);
    }

    public function purchaseReport(Request $request)
    {
        $query = Purchase::with('supplier');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('purchase_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $purchases = $query->get();

        return response()->json([
            'total_purchases' => $purchases->sum('total_amount'),
            'data' => $purchases
        ]);
    }

    public function inventoryValuation()
    {
        $products = Product::select(
            'id',
            'name',
            'stock_quantity',
            'cost_price'
        )->get();

        $totalValue = $products->sum(function ($product) {
            return $product->stock_quantity * $product->cost_price;
        });

        return response()->json([
            'total_inventory_value' => $totalValue,
            'products' => $products
        ]);
    }

    public function lowStock()
    {
        $products = Product::whereColumn(
            'stock_quantity',
            '<=',
            'alert_quantity'
        )->get();

        return response()->json($products);
    }


    

}





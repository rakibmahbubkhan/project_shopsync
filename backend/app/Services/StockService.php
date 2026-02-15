<?
namespace App\Services;

use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function increaseStock($productId, $quantity, $referenceType, $referenceId, $userId)
    {
        DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId, $userId) {

            $product = Product::lockForUpdate()->findOrFail($productId);

            $product->increment('stock_quantity', $quantity);

            StockLog::create([
                'product_id'     => $productId,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'type'           => 'in',
                'quantity'       => $quantity,
                'created_by'     => $userId
            ]);
        });
    }

    public function decreaseStock($productId, $quantity, $referenceType, $referenceId, $userId)
    {
        DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId, $userId) {

            $product = Product::lockForUpdate()->findOrFail($productId);

            if ($product->stock_quantity < $quantity) {
                throw new \Exception('Insufficient stock');
            }

            $product->decrement('stock_quantity', $quantity);

            StockLog::create([
                'product_id'     => $productId,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'type'           => 'out',
                'quantity'       => $quantity,
                'created_by'     => $userId
            ]);
        });
    }
}

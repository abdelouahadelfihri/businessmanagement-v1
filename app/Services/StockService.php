<?php

namespace App\Services;

use App\Models\MasterData\StockMovement;
use App\Models\MasterData\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Exception;

class StockService
{
    public function move(
        int $productId,
        int $warehouseId,
        int $quantity,
        string $type,
        string $referenceType,
        int $referenceId,
        string $note = null
    ) {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $type, $referenceType, $referenceId, $note) {
            $stock = WarehouseStock::firstOrCreate(
                ['warehouse_id' => $warehouseId, 'product_id' => $productId],
                ['quantity' => 0]
            );


            if ($type === 'out' && $stock->quantity < $quantity) {
                throw new Exception('Insufficient stock');
            }


            $stock->quantity += ($type === 'in') ? $quantity : -$quantity;
            $stock->save();


            StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
            ]);
        });
    }
}
<?php

namespace App\Traits;

use App\Models\MasterData\WarehouseStock;

trait StockHelper
{
    public function updateStock($productId, $warehouseId, $qty, $type)
    {
        $stock = WarehouseStock::firstOrCreate(
            [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId
            ],
            [
                'quantity' => 0
            ]
        );

        if ($type === 'in') {
            $stock->quantity += $qty;
        } else {
            $stock->quantity -= $qty;
        }

        $stock->save();
    }
}
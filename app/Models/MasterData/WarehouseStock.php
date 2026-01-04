<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Product;
use App\Models\MasterData\Warehouse;

class WarehouseStock extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
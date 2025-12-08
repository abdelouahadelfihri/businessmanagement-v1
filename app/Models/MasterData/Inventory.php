<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $primaryKey = 'inventory_id';

    protected $fillable = [
        'inventory_id',
        'quantity_available',
        'minimum_stock_level',
        'maximum_stock_level',
        'reorder_point',
        'product_id',
        'warehouse_id',
    ];

    // Relationship: Inventory → Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relationship: Inventory → Warehouse
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\StockMovement;

class SaleReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'customer_id',
        'return_number',
        'date',
        'warehouse_id',
        'reason',
        'subtotal',
        'tax',
        'total',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\MasterData\Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(\App\Models\Sales\SaleOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(SaleReturnLine::class, 'sale_return_id');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}
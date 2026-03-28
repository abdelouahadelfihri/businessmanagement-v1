<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\StockMovement;

class SaleDelivery extends Model
{
    use HasFactory;

    protected $table = 'sales_deliveries';

    protected $fillable = [
        'sales_order_id',
        'delivery_number',
        'date',
        'status',
        'total',
        'warehouse_id', // add warehouse
    ];

    // Sales order
    public function salesOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'sales_order_id');
    }

    // Warehouse
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // Lines
    public function lines()
    {
        return $this->hasMany(SaleDeliveryLine::class, 'sale_delivery_id');
    }

    // Stock movements
    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}
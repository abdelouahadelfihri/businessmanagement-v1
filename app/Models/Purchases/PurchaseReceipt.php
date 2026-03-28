<?php

namespace App\Models\Purchases;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\StockMovement;

class PurchaseReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'supplier_id',
        'receipt_number',
        'date',
        'total',
        'status',
        'warehouse_id', // <- add warehouse
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last = PurchaseReceipt::orderBy('id', 'desc')->first();
            $nextId = $last ? $last->id + 1 : 1;

            $model->receipt_number = 'GR-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        });
    }

    // Purchase order
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    // Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // Warehouse
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // Lines
    public function lines()
    {
        return $this->hasMany(PurchaseReceiptLine::class);
    }

    // Stock movements
    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}
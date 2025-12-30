<?php

namespace App\Models\Purchases;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Purchases\PurchaseOrder;
class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'supplier_id',
        'invoice_number',
        'date',
        'subtotal',
        'tax',
        'total',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last = static::orderBy('id', 'desc')->first();
            $nextId = $last ? $last->id + 1 : 1;
            $model->invoice_number = 'PO-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        });
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\MasterData\Supplier::class, 'supplier_id');
    }
}
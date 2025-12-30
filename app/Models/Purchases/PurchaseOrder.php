<?php

namespace App\Models\Purchases;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'po_number',
        'request_id',
        'order_date',
        'status',
        'total_amount',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last = static::orderBy('id', 'desc')->first();
            $nextId = $last ? $last->id + 1 : 1;
            $model->po_number = 'PO-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        });
    }
    /**
     * Relationships
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\MasterData\Supplier::class);
    }

    public function request()
    {
        return $this->belongsTo(\App\Models\Purchases\PurchaseRequest::class, 'request_id');
    }
}
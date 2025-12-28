<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleQuotation extends Model
{
    use HasFactory;

    protected $table = 'sales_quotes';

    protected $fillable = [
        'customer_id',
        'quote_number',
        'date',
        'total',
        'status'
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last = SaleQuotation::orderBy('id', 'desc')->first();
            $nextId = $last ? $last->id + 1 : 1;

            $model->quote_number = 'QT-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        });
    }
    public function customer()
    {
        return $this->belongsTo(\App\Models\MasterData\Customer::class, 'customer_id');
    }
}
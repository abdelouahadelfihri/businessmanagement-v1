<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesQuote extends Model
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

    public function customer()
    {
        return $this->belongsTo(\App\Models\Purchase\Customer::class, 'customer_id');
    }
}
<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payable_id',
        'payable_type',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
    ];

    // Polymorphic relation
    public function payable()
    {
        return $this->morphTo();
    }
}
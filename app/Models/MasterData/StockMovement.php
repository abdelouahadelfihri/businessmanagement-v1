<?php

namespace App\Models\MasterData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'tax_id',
        'bank_details',
        'notes',
        'discount'
    ];
}

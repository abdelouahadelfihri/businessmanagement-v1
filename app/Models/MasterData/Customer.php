<?php

namespace App\Models\MasterData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

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

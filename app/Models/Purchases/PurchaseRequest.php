<?php

namespace App\Models\Purchases;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    // Optional: if table name matches 'purchase_requests', Laravel auto-detects it
    protected $table = 'purchase_requests';

    protected $fillable = [
        'supplier_id',
        'description',
        'date',
        'status',
    ];

    // Relationship to Supplier
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Purchases\Supplier::class);
    }

}
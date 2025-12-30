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
        'pr_number',
        'description',
        'date',
        'status',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last = static::orderBy('id', 'desc')->first();
            $nextId = $last ? $last->id + 1 : 1;
            $model->pr_number = 'PR-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        });
    }
    // Relationship to Supplier
    public function supplier()
    {
        return $this->belongsTo(\App\Models\MasterData\Supplier::class);
    }

}
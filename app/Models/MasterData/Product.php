<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'code',
        'bar_code',
        'category',
        'unit',
        'reorder_level',
        'is_active',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function getCurrentStockAttribute()
    {
        return \App\Models\MasterData\StockMovement::where('product_id', $this->id)
            ->selectRaw("
            COALESCE(SUM(
                CASE 
                    WHEN type = 'in' THEN quantity
                    WHEN type = 'out' THEN -quantity
                END
            ),0) as stock
        ")
            ->value('stock');
    }
}
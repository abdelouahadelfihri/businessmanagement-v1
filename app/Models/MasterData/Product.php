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
    public function categoryRelation()
    {
        return $this->belongsTo(Category::class, 'category', 'id');
    }

    public function unitRelation()
    {
        return $this->belongsTo(Unit::class, 'unit', 'unit_id');
    }
}
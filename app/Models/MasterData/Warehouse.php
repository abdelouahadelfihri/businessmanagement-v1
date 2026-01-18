<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Location;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';

    protected $fillable = [
        'name',
        'is_refrigerated',
        'location_owner_id',
    ];

    protected $casts = [
        'is_refrigerated' => 'boolean',
    ];

    /**
     * Warehouse belongs to a Location
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_owner_id', 'location_id');
    }
}
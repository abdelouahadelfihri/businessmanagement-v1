<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'warehouses';

    protected $fillable = [
        'name',
        'is_refrigerated',
        'location_owner_id',
    ];

    // Relationship: Warehouse belongs to Location
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_owner_id', 'location_id');
    }

    // Relationship: Warehouse has many Transfers as origin
    public function originTransfers()
    {
        return $this->hasMany(Transfer::class, 'origin_warehouse_id', 'id');
    }

    // Relationship: Warehouse has many Transfers as destination
    public function destinationTransfers()
    {
        return $this->hasMany(Transfer::class, 'destination_warehouse_id', 'id');
    }
}
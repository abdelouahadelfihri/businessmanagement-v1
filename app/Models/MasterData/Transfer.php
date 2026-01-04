<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\TransferLine;
class Transfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'transfer_date',
        'status',
        'remarks'
    ];

    public function lines()
    {
        return $this->hasMany(TransferLine::class);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    protected static function booted()
    {
        static::creating(function ($transfer) {
            $last = self::latest()->first();
            $next = $last ? $last->id + 1 : 1;
            $transfer->transfer_number = 'TR-' . date('Y') . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
        });
    }
}

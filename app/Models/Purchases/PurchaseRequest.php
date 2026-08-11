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
        'requested_by',      // user who created the request
        'pr_number',
        'description',
        'date',
        'expected_date',      // when items/services are needed
        'priority',           // low, medium, high, urgent
        'status',              // draft, pending, approved, rejected, ordered, completed
        'total_amount',
        'currency',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
        'attachment',
    ];

    protected $casts = [
        'date' => 'date',
        'expected_date' => 'date',
        'approved_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lines()
    {
        return $this->hasMany(PurchaseRequestLine::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last = static::orderBy('id', 'desc')->first();
            $nextId = $last ? $last->id + 1 : 1;
            $model->pr_number = 'PR-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        });
    }
    

}
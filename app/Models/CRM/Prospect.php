<?php

namespace App\Models\CRM;

use App\Models\Concerns\HasContacts;
use App\Models\MasterData\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prospect extends Model
{
    use HasFactory, HasContacts;

    protected $table = 'prospects';

    public const TYPES = ['company' => 'Company', 'individual' => 'Individual'];

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'proposal' => 'Proposal sent',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
    ];

    public const STATUS_COLORS = [
        'new' => 'secondary',
        'contacted' => 'info',
        'qualified' => 'primary',
        'proposal' => 'warning',
        'negotiation' => 'orange',
        'won' => 'success',
        'lost' => 'danger',
    ];

    /** Default win probability (%) suggested for each stage. */
    public const STATUS_PROBABILITY = [
        'new' => 10, 'contacted' => 20, 'qualified' => 40, 'proposal' => 60,
        'negotiation' => 80, 'won' => 100, 'lost' => 0,
    ];

    public const OPEN_STATUSES = ['new', 'contacted', 'qualified', 'proposal', 'negotiation'];

    public const SOURCES = [
        'website' => 'Website',
        'referral' => 'Referral',
        'cold_call' => 'Cold call',
        'email' => 'Email campaign',
        'social_media' => 'Social media',
        'trade_show' => 'Trade show',
        'advertising' => 'Advertising',
        'other' => 'Other',
    ];

    public const CURRENCIES = ['MAD', 'EUR', 'USD'];

    protected $fillable = [
        'code', 'type', 'name', 'industry',
        'email', 'phone', 'website', 'address', 'city', 'country',
        'source', 'status', 'estimated_value', 'currency', 'probability',
        'expected_close_date', 'next_follow_up_date', 'assigned_to', 'lost_reason', 'notes',
        'converted_customer_id', 'converted_at',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'next_follow_up_date' => 'date',
        'converted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (Prospect $prospect) {
            if (blank($prospect->code)) {
                $prospect->code = sprintf('PRO-%05d', $prospect->id);
                $prospect->saveQuietly();
            }
        });
    }

    /* ---------------- Relations ---------------- */

    public function convertedCustomer()
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    /* ---------------- Scopes ---------------- */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $like = '%' . $term . '%';
            $q->where('name', 'like', $like)
                ->orWhere('code', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('industry', 'like', $like)
                ->orWhereHas('contacts', fn($c) => $c->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like));
        });
    }

    /* ---------------- Helpers ---------------- */

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function isConverted(): bool
    {
        return $this->converted_customer_id !== null;
    }

    public function getWeightedValueAttribute(): float
    {
        return round((float) $this->estimated_value * $this->probability / 100, 2);
    }

    public function getFollowUpOverdueAttribute(): bool
    {
        return $this->isOpen() && $this->next_follow_up_date && $this->next_follow_up_date->lt(today());
    }
}

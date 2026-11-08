<?php

namespace App\Models\MasterData;

use App\Models\Sales\SaleInvoice;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleQuotation;
use App\Models\Sales\SaleReturn;
use App\Models\Concerns\HasContacts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, HasContacts;

    protected $table = 'customers';

    public const TYPES = [
        'company' => 'Company',
        'individual' => 'Individual',
    ];

    public const CURRENCIES = ['MAD', 'EUR', 'USD'];

    protected $fillable = [
        'code', 'type', 'name',
        'email', 'phone', 'mobile', 'website',
        'address', 'city', 'postal_code', 'country', 'shipping_address',
        'ice', 'tax_id', 'trade_register', 'patente',
        'payment_terms_days', 'credit_limit', 'discount_percent', 'currency',
        'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'payment_terms_days' => 'integer',
        'credit_limit' => 'decimal:2',
        'discount_percent' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Auto-assign a code (CUS-00001) when none was typed in
        static::created(function (Customer $customer) {
            if (blank($customer->code)) {
                $customer->code = sprintf('CUS-%05d', $customer->id);
                $customer->saveQuietly();
            }
        });
    }

    /* ---------------- Relations ---------------- */

    public function quotations()
    {
        return $this->hasMany(SaleQuotation::class);
    }

    public function orders()
    {
        return $this->hasMany(SaleOrder::class);
    }

    public function invoices()
    {
        return $this->hasMany(SaleInvoice::class);
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    /** The prospect this customer was converted from, if any. */
    public function sourceProspect()
    {
        return $this->hasOne(\App\Models\CRM\Prospect::class, 'converted_customer_id');
    }

    /* ---------------- Scopes ---------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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
                ->orWhereHas('contacts', fn(Builder $c) => $c->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like)->orWhere('email', 'like', $like))
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('mobile', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('ice', 'like', $like);
        });
    }

    /* ---------------- Helpers ---------------- */

    /** Amount still owed by this customer on issued / partially paid invoices. */
    public function outstandingBalance(): float
    {
        return (float) $this->invoices()->whereIn('status', ['issued', 'partial'])->sum('balance_due');
    }

    /** True when the customer is linked to any sales document (so it must not be deleted). */
    public function hasDocuments(): bool
    {
        return $this->quotations()->exists()
            || $this->orders()->exists()
            || $this->invoices()->exists()
            || $this->returns()->exists();
    }

    public function getFullAddressAttribute(): ?string
    {
        $cityLine = trim(($this->postal_code ? $this->postal_code . ' ' : '') . ($this->city ?? ''));
        $parts = array_filter([$this->address, $cityLine, $this->country]);

        return $parts ? implode("\n", $parts) : null;
    }
}

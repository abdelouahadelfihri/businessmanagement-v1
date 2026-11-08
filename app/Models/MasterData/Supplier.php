<?php

namespace App\Models\MasterData;

use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseOrder;
use App\Models\Purchases\PurchaseReceipt;
use App\Models\Purchases\PurchaseRequest;
use App\Models\Purchases\PurchaseReturn;
use App\Models\Concerns\HasContacts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory, HasContacts;

    protected $table = 'suppliers';

    public const TYPES = [
        'company' => 'Company',
        'individual' => 'Individual',
    ];

    public const CURRENCIES = ['MAD', 'EUR', 'USD'];

    public const PAYMENT_METHODS = PurchaseInvoice::PAYMENT_METHODS;

    protected $fillable = [
        'code', 'type', 'name',
        'email', 'phone', 'mobile', 'website',
        'address', 'city', 'postal_code', 'country',
        'ice', 'tax_id', 'trade_register', 'patente',
        'bank_name', 'rib', 'swift',
        'payment_terms_days', 'payment_method', 'currency', 'lead_time_days', 'minimum_order', 'discount_percent',
        'rating', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'payment_terms_days' => 'integer',
        'lead_time_days' => 'integer',
        'rating' => 'integer',
        'minimum_order' => 'decimal:2',
        'discount_percent' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Auto-assign a code (SUP-00001) when none was typed in
        static::created(function (Supplier $supplier) {
            if (blank($supplier->code)) {
                $supplier->code = sprintf('SUP-%05d', $supplier->id);
                $supplier->saveQuietly();
            }
        });
    }

    /* ---------------- Relations ---------------- */

    public function requests()
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function orders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function receipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function invoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
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

    /** Amount we still owe this supplier on validated invoices. */
    public function amountDue(): float
    {
        return (float) $this->invoices()
            ->whereNotNull('validated_at')
            ->selectRaw('COALESCE(SUM(total - COALESCE(amount_paid, 0)), 0) as due')
            ->value('due');
    }

    /** True when the supplier is linked to any purchase document (so it must not be deleted). */
    public function hasDocuments(): bool
    {
        return $this->requests()->exists()
            || $this->orders()->withTrashed()->exists()
            || $this->receipts()->exists()
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

<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contacts';

    public const SALUTATIONS = ['mr' => 'Mr.', 'mrs' => 'Mrs.', 'ms' => 'Ms.', 'dr' => 'Dr.'];

    public const CHANNELS = ['email' => 'Email', 'phone' => 'Phone', 'mobile' => 'Mobile', 'whatsapp' => 'WhatsApp'];

    /** Owner types (morph map aliases) with their label, route prefix and icon. */
    public const OWNER_TYPES = [
        'customer' => ['label' => 'Customer', 'route' => 'masterdata.customers', 'icon' => 'bi-person-badge', 'color' => 'primary'],
        'supplier' => ['label' => 'Supplier', 'route' => 'masterdata.suppliers', 'icon' => 'bi-truck', 'color' => 'purple'],
        'prospect' => ['label' => 'Prospect', 'route' => 'crm.prospects', 'icon' => 'bi-bullseye', 'color' => 'warning'],
    ];

    protected $fillable = [
        'contactable_type', 'contactable_id',
        'salutation', 'first_name', 'last_name', 'job_title', 'department',
        'email', 'phone', 'mobile', 'preferred_channel',
        'is_primary', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Only one primary contact per owner
        static::saved(function (Contact $contact) {
            if ($contact->is_primary) {
                static::where('contactable_type', $contact->contactable_type)
                    ->where('contactable_id', $contact->contactable_id)
                    ->whereKeyNot($contact->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });

        // The first contact of an owner becomes primary automatically
        static::creating(function (Contact $contact) {
            if (! $contact->is_primary && ! static::where('contactable_type', $contact->contactable_type)
                    ->where('contactable_id', $contact->contactable_id)->exists()) {
                $contact->is_primary = true;
            }
        });
    }

    /* ---------------- Quick "primary contact" on owner create forms ---------------- */

    /** Validation rules for the optional primary_contact[...] block of an owner form. */
    public static function quickRules(): array
    {
        return [
            'primary_contact.first_name' => 'nullable|string|max:100',
            'primary_contact.last_name' => 'nullable|string|max:100',
            'primary_contact.job_title' => 'nullable|string|max:100',
            'primary_contact.email' => 'nullable|email|max:255',
            'primary_contact.mobile' => 'nullable|string|max:30',
        ];
    }

    /** Readable field names for the primary_contact[...] block in validation messages. */
    public static function quickAttributes(): array
    {
        return [
            'primary_contact.first_name' => 'contact first name',
            'primary_contact.last_name' => 'contact last name',
            'primary_contact.job_title' => 'contact job title',
            'primary_contact.email' => 'contact email',
            'primary_contact.mobile' => 'contact mobile',
        ];
    }

    /** Creates the owner's primary contact when a first name was given. */
    public static function createPrimaryFor(Model $owner, ?array $data): ?self
    {
        if (blank($data['first_name'] ?? null)) {
            return null;
        }

        return $owner->contacts()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'preferred_channel' => filled($data['mobile'] ?? null) && blank($data['email'] ?? null) ? 'mobile' : 'email',
            'is_primary' => true,
        ]);
    }

    /* ---------------- Relations ---------------- */

    public function contactable()
    {
        return $this->morphTo();
    }

    /* ---------------- Scopes ---------------- */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $like = '%' . $term . '%';
            $q->where('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(last_name, '')) like ?", [$like])
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('mobile', 'like', $like)
                ->orWhere('job_title', 'like', $like);

            // Match on the owner's name too
            foreach (self::OWNER_TYPES as $alias => $meta) {
                $model = Relation::getMorphedModel($alias);
                $q->orWhere(fn($o) => $o->where('contactable_type', $alias)
                    ->whereIn('contactable_id', $model::where('name', 'like', $like)->select('id')));
            }
        });
    }

    /* ---------------- Helpers ---------------- */

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getDisplayNameAttribute(): string
    {
        $prefix = self::SALUTATIONS[$this->salutation] ?? null;

        return trim(($prefix ? $prefix . ' ' : '') . $this->full_name);
    }

    public function getInitialsAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->first_name, 0, 1) . mb_substr((string) $this->last_name, 0, 1));
    }

    public function getOwnerMetaAttribute(): array
    {
        return self::OWNER_TYPES[$this->contactable_type] ?? ['label' => ucfirst($this->contactable_type), 'route' => null, 'icon' => 'bi-building', 'color' => 'secondary'];
    }

    /** URL of the owner's detail page. */
    public function getOwnerUrlAttribute(): ?string
    {
        $route = $this->owner_meta['route'];

        return $route && $this->contactable ? route($route . '.show', $this->contactable) : null;
    }

    /** WhatsApp link built from the mobile number (Moroccan numbers get +212). */
    public function getWhatsappUrlAttribute(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) ($this->mobile ?: $this->phone));
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '212' . substr($digits, 1);
        }

        return 'https://wa.me/' . $digits;
    }
}

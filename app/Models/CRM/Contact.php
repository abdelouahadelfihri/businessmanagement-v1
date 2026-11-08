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

    /* ---------------- Contacts editor embedded in owner forms ---------------- */

    /** Fields edited inline on the customer / supplier / prospect forms. */
    public const EDITOR_FIELDS = ['first_name', 'last_name', 'job_title', 'email', 'phone', 'mobile'];

    /** Validation rules for the contacts[...] rows of an owner form. */
    public static function editorRules(): array
    {
        $others = 'contacts.*.last_name,contacts.*.job_title,contacts.*.email,contacts.*.phone,contacts.*.mobile';

        return [
            'contacts' => 'nullable|array|max:50',
            'contacts.*.id' => 'nullable|integer',
            'contacts.*._delete' => 'nullable|boolean',
            'contacts.*.first_name' => "nullable|required_with:$others|string|max:100",
            'contacts.*.last_name' => 'nullable|string|max:100',
            'contacts.*.job_title' => 'nullable|string|max:100',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:30',
            'contacts.*.mobile' => 'nullable|string|max:30',
            'primary_contact_row' => 'nullable|string|max:20',
        ];
    }

    public static function editorMessages(): array
    {
        return ['contacts.*.first_name.required_with' => 'Each contact needs a first name (or clear the row to ignore it).'];
    }

    /** Readable field names for validation messages. */
    public static function editorAttributes(): array
    {
        return [
            'contacts.*.first_name' => 'contact first name',
            'contacts.*.last_name' => 'contact last name',
            'contacts.*.job_title' => 'contact job title',
            'contacts.*.email' => 'contact email',
            'contacts.*.phone' => 'contact phone',
            'contacts.*.mobile' => 'contact mobile',
        ];
    }

    /**
     * Applies the contacts[...] rows of an owner form: updates existing contacts,
     * creates new ones, deletes rows flagged with _delete, ignores blank rows,
     * and marks the chosen row ($primaryRow = row key) as the primary contact.
     */
    public static function syncFor(Model $owner, ?array $rows, ?string $primaryRow = null): void
    {
        $primary = null;

        foreach ($rows ?? [] as $key => $row) {
            $existing = filled($row['id'] ?? null)
                ? $owner->contacts()->whereKey((int) $row['id'])->first()   // only this owner's contacts
                : null;

            if (! empty($row['_delete']) || blank($row['first_name'] ?? null)) {
                $existing?->delete();
                continue;
            }

            $data = array_intersect_key($row, array_flip(self::EDITOR_FIELDS));

            if ($existing) {
                $existing->update($data);
                $contact = $existing;
            } else {
                $data['preferred_channel'] = filled($data['mobile'] ?? null) && blank($data['email'] ?? null) ? 'mobile' : 'email';
                $contact = $owner->contacts()->create($data + ['is_active' => true, 'is_primary' => false]);
            }

            if ((string) $key === (string) $primaryRow) {
                $primary = $contact;
            }
        }

        // Exactly one primary contact when the owner has any
        $primary ??= $owner->contacts()->where('is_primary', true)->first() ?? $owner->contacts()->first();
        $primary?->update(['is_primary' => true]);
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

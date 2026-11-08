<?php

namespace App\Models\Concerns;

use App\Models\CRM\Contact;

/**
 * For models that own contacts (customers, suppliers, prospects).
 */
trait HasContacts
{
    public static function bootHasContacts(): void
    {
        // Contacts have no FK (polymorphic owner), so remove them with their owner
        static::deleting(fn($owner) => $owner->contacts()->delete());
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable')
            ->orderByDesc('is_primary')->orderBy('first_name');
    }

    public function primaryContact()
    {
        return $this->morphOne(Contact::class, 'contactable')->where('is_primary', true);
    }
}

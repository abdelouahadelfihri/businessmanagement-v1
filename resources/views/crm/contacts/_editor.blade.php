{{--
    Repeatable contacts editor embedded in the customer / supplier / prospect forms.
    Expects $owner (new or existing model). Rows are posted as contacts[<key>][...]
    and the primary one as primary_contact_row=<key>; the controller calls Contact::syncFor().
--}}
@php
    if (old('contacts') !== null || old('_token')) {
        $rows = old('contacts', []);
        $primaryRow = old('primary_contact_row');
    } elseif ($owner->exists) {
        $rows = [];
        $primaryRow = null;
        foreach ($owner->contacts as $c) {
            $rows['e' . $c->id] = $c->only(array_merge(['id'], \App\Models\CRM\Contact::EDITOR_FIELDS));
            if ($c->is_primary) {
                $primaryRow = 'e' . $c->id;
            }
        }
    } else {
        $rows = ['n1' => []];   // one empty row to start with
        $primaryRow = 'n1';
    }
    $primaryRow ??= array_key_first($rows);
    $fieldErr = fn($key, $field) => $errors->has("contacts.$key.$field") ? 'is-invalid' : '';
@endphp

<div class="card shadow-sm mb-3" id="contactsEditor">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-lines-fill"></i> Contacts</span>
        <button type="button" class="btn btn-sm btn-outline-primary" id="addContactRow">
            <i class="bi bi-person-plus"></i> Add contact
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            People you deal with at this company. Mark one as <strong>primary</strong>; leave a row empty to ignore it.
        </p>

        <div id="contactRows" class="d-flex flex-column gap-3">
            @foreach($rows as $key => $row)
                @include('crm.contacts._editor_row', ['key' => $key, 'row' => $row, 'isPrimary' => (string) $key === (string) $primaryRow])
            @endforeach
        </div>

        <div id="noContacts" class="text-muted small {{ count($rows) ? 'd-none' : '' }}">No contacts yet.</div>
    </div>
</div>

<template id="contactRowTemplate">
    @include('crm.contacts._editor_row', ['key' => '__KEY__', 'row' => [], 'isPrimary' => false])
</template>

@push('scripts')
    <script>
        (function () {
            const list = document.getElementById('contactRows');
            const template = document.getElementById('contactRowTemplate').innerHTML;
            const empty = document.getElementById('noContacts');
            let counter = Date.now() % 100000;

            const visibleRows = () => [...list.querySelectorAll('.contact-row:not(.d-none)')];

            function refresh() {
                const rows = visibleRows();
                empty.classList.toggle('d-none', rows.length > 0);
                // Keep a primary selected when rows exist
                if (rows.length && !rows.some(r => r.querySelector('.primary-radio').checked)) {
                    rows[0].querySelector('.primary-radio').checked = true;
                }
                rows.forEach((r, i) => r.querySelector('.row-number').textContent = i + 1);
            }

            document.getElementById('addContactRow').addEventListener('click', () => {
                const key = 'n' + (++counter);
                list.insertAdjacentHTML('beforeend', template.replaceAll('__KEY__', key));
                refresh();
                list.lastElementChild.querySelector('input[type=text]').focus();
            });

            list.addEventListener('click', e => {
                const btn = e.target.closest('.remove-contact');
                if (!btn) return;
                const row = btn.closest('.contact-row');
                const idInput = row.querySelector('.contact-id');

                if (idInput && idInput.value) {
                    // Existing contact: flag for deletion on save
                    row.querySelector('.contact-delete').value = 1;
                    row.classList.add('d-none');
                    row.querySelector('.primary-radio').checked = false;
                    document.getElementById('contactsEditor').querySelector('.card-body > p').insertAdjacentHTML('afterend',
                        '<div class="alert alert-warning py-2 small removed-note">A contact will be deleted when you save.</div>');
                } else {
                    row.remove();
                }
                refresh();
            });

            refresh();
        })();
    </script>
@endpush

<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Contact;
use App\Models\CRM\Prospect;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $contacts = Contact::query()
            ->with('contactable')
            ->search($request->input('search'))
            ->when($request->filled('owner_type'), fn($q) => $q->where('contactable_type', $request->input('owner_type')))
            ->when($request->boolean('primary'), fn($q) => $q->where('is_primary', true))
            ->when($request->input('status') === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('first_name')->orderBy('last_name')
            ->paginate(20)
            ->withQueryString();

        $counts = Contact::selectRaw('contactable_type, COUNT(*) as total')->groupBy('contactable_type')->pluck('total', 'contactable_type');

        return view('crm.contacts.index', compact('contacts', 'counts'));
    }

    public function create(Request $request)
    {
        $contact = new Contact(['is_active' => true, 'preferred_channel' => 'email']);

        if ($owner = $this->ownerFromKey($request->query('owner'))) {
            $contact->contactable_type = $owner->getMorphClass();
            $contact->contactable_id = $owner->getKey();
        }

        return view('crm.contacts.create', compact('contact') + $this->formData());
    }

    public function store(Request $request)
    {
        $contact = Contact::create($this->validated($request));

        return $this->redirectAfterSave($request, $contact, "Contact {$contact->full_name} added.");
    }

    public function show(Contact $contact)
    {
        $contact->load('contactable');

        $colleagues = Contact::where('contactable_type', $contact->contactable_type)
            ->where('contactable_id', $contact->contactable_id)
            ->whereKeyNot($contact->id)
            ->orderByDesc('is_primary')->orderBy('first_name')
            ->get();

        return view('crm.contacts.show', compact('contact', 'colleagues'));
    }

    public function edit(Contact $contact)
    {
        return view('crm.contacts.edit', compact('contact') + $this->formData());
    }

    public function update(Request $request, Contact $contact)
    {
        $contact->update($this->validated($request));

        return $this->redirectAfterSave($request, $contact, "Contact {$contact->full_name} updated.");
    }

    public function destroy(Request $request, Contact $contact)
    {
        $owner = $contact->contactable;
        $wasPrimary = $contact->is_primary;
        $contact->delete();

        // Keep a primary contact when others remain
        if ($wasPrimary && $owner && ($next = $owner->contacts()->first())) {
            $next->update(['is_primary' => true]);
        }

        if ($request->input('return') === 'owner' && $owner) {
            return redirect()->to($this->ownerUrl($owner) . '#contacts')->with('success', "Contact {$contact->full_name} deleted.");
        }

        return redirect()->route('crm.contacts.index')->with('success', "Contact {$contact->full_name} deleted.");
    }

    /** Make this contact the primary one for its company. */
    public function makePrimary(Request $request, Contact $contact)
    {
        $contact->update(['is_primary' => true]);

        return back()->with('success', "{$contact->full_name} is now the primary contact.");
    }

    /* ------------------------------------------------------------------ */

    private function formData(): array
    {
        return [
            'owners' => [
                'customer' => Customer::orderBy('name')->get(['id', 'name', 'code']),
                'supplier' => Supplier::orderBy('name')->get(['id', 'name', 'code']),
                'prospect' => Prospect::whereNull('converted_customer_id')->orderBy('name')->get(['id', 'name', 'code']),
            ],
        ];
    }

    /** "customer:12" -> Customer #12 */
    private function ownerFromKey(?string $key): ?Model
    {
        if (! $key || ! str_contains($key, ':')) {
            return null;
        }

        [$type, $id] = explode(':', $key, 2);
        $class = array_key_exists($type, Contact::OWNER_TYPES) ? Relation::getMorphedModel($type) : null;

        return $class ? $class::find((int) $id) : null;
    }

    private function ownerUrl(Model $owner): string
    {
        $route = Contact::OWNER_TYPES[$owner->getMorphClass()]['route'];

        return route($route . '.show', $owner);
    }

    private function redirectAfterSave(Request $request, Contact $contact, string $message)
    {
        if ($request->input('return') === 'owner' && $contact->contactable) {
            return redirect()->to($this->ownerUrl($contact->contactable) . '#contacts')->with('success', $message);
        }

        return redirect()->route('crm.contacts.show', $contact)->with('success', $message);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'owner' => 'required|string',
            'salutation' => ['nullable', Rule::in(array_keys(Contact::SALUTATIONS))],
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'preferred_channel' => ['required', Rule::in(array_keys(Contact::CHANNELS))],
            'notes' => 'nullable|string|max:5000',
        ], [
            'owner.required' => 'Choose the company this contact belongs to.',
        ]);

        $owner = $this->ownerFromKey($data['owner']);
        if (! $owner) {
            throw ValidationException::withMessages(['owner' => 'The selected company does not exist.']);
        }
        unset($data['owner']);

        $data['contactable_type'] = $owner->getMorphClass();
        $data['contactable_id'] = $owner->getKey();
        $data['is_primary'] = $request->boolean('is_primary');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

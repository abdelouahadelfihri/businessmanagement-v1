<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Contact;
use App\Models\CRM\Prospect;
use App\Models\MasterData\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProspectController extends Controller
{
    public function index(Request $request)
    {
        $prospects = Prospect::query()
            ->with('primaryContact')
            ->search($request->input('search'))
            ->when($request->filled('status'), function ($q) use ($request) {
                $request->input('status') === 'open'
                    ? $q->open()
                    : $q->where('status', $request->input('status'));
            })
            ->when($request->filled('source'), fn($q) => $q->where('source', $request->input('source')))
            ->when($request->filled('assigned_to'), fn($q) => $q->where('assigned_to', $request->input('assigned_to')))
            ->when($request->boolean('follow_up'), fn($q) => $q->open()->whereDate('next_follow_up_date', '<=', today()))
            ->orderByRaw('CASE WHEN status IN ("won", "lost") THEN 1 ELSE 0 END')
            ->orderByRaw('next_follow_up_date IS NULL, next_follow_up_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Pipeline summary (all prospects, not just this page)
        $pipeline = Prospect::selectRaw('status, COUNT(*) as total, COALESCE(SUM(estimated_value), 0) as value')
            ->groupBy('status')->get()->keyBy('status');

        $summary = [
            'open' => Prospect::open()->count(),
            'open_value' => (float) Prospect::open()->sum('estimated_value'),
            'weighted' => (float) Prospect::open()->selectRaw('COALESCE(SUM(estimated_value * probability / 100), 0) as w')->value('w'),
            'follow_ups' => Prospect::open()->whereDate('next_follow_up_date', '<=', today())->count(),
            'won' => (int) ($pipeline['won']->total ?? 0),
            'lost' => (int) ($pipeline['lost']->total ?? 0),
        ];
        $closed = $summary['won'] + $summary['lost'];
        $summary['win_rate'] = $closed ? round($summary['won'] / $closed * 100) : null;

        $assignees = Prospect::whereNotNull('assigned_to')->distinct()->orderBy('assigned_to')->pluck('assigned_to');

        return view('crm.prospects.index', compact('prospects', 'pipeline', 'summary', 'assignees'));
    }

    public function create()
    {
        $prospect = new Prospect(['status' => 'new', 'probability' => 10, 'source' => 'other', 'currency' => 'MAD', 'country' => 'Morocco']);

        return view('crm.prospects.create', compact('prospect'));
    }

    public function store(Request $request)
    {
        $prospect = DB::transaction(function () use ($request) {
            $prospect = Prospect::create($this->validated($request));
            Contact::createPrimaryFor($prospect, $request->input('primary_contact'));

            return $prospect;
        });

        return redirect()->route('crm.prospects.show', $prospect)->with('success', "Prospect {$prospect->name} created.");
    }

    public function show(Prospect $prospect)
    {
        $prospect->load(['contacts', 'convertedCustomer']);

        return view('crm.prospects.show', compact('prospect'));
    }

    public function edit(Prospect $prospect)
    {
        return view('crm.prospects.edit', compact('prospect'));
    }

    public function update(Request $request, Prospect $prospect)
    {
        $prospect->update($this->validated($request, $prospect));

        return redirect()->route('crm.prospects.show', $prospect)->with('success', "Prospect {$prospect->name} updated.");
    }

    public function destroy(Prospect $prospect)
    {
        $prospect->delete(); // contacts are removed by HasContacts

        return redirect()->route('crm.prospects.index')->with('success', "Prospect {$prospect->name} deleted.");
    }

    /** Move the prospect to another pipeline stage. */
    public function updateStatus(Request $request, Prospect $prospect)
    {
        abort_if($prospect->isConverted(), 422, 'This prospect has already been converted.');

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Prospect::STATUSES))],
            'lost_reason' => 'nullable|required_if:status,lost|string|max:255',
        ], ['lost_reason.required_if' => 'Please give a reason when marking the prospect as lost.']);

        $prospect->update([
            'status' => $data['status'],
            'probability' => Prospect::STATUS_PROBABILITY[$data['status']],
            'lost_reason' => $data['status'] === 'lost' ? $data['lost_reason'] : null,
        ]);

        return back()->with('success', "Status changed to " . Prospect::STATUSES[$data['status']] . '.');
    }

    /** Turn the prospect into a customer and move its contacts over. */
    public function convert(Prospect $prospect)
    {
        if ($prospect->isConverted()) {
            return back()->with('error', 'This prospect has already been converted.');
        }

        $customer = DB::transaction(function () use ($prospect) {
            $customer = Customer::create([
                'type' => $prospect->type,
                'name' => $prospect->name,
                'email' => $prospect->email,
                'phone' => $prospect->phone,
                'website' => $prospect->website,
                'address' => $prospect->address,
                'city' => $prospect->city,
                'country' => $prospect->country ?: 'Morocco',
                'currency' => $prospect->currency,
                'payment_terms_days' => 30,
                'notes' => trim("Converted from prospect {$prospect->code}.\n" . $prospect->notes),
                'is_active' => true,
            ]);

            $prospect->contacts()->update([
                'contactable_type' => $customer->getMorphClass(),
                'contactable_id' => $customer->id,
            ]);

            $prospect->update([
                'status' => 'won',
                'probability' => 100,
                'converted_customer_id' => $customer->id,
                'converted_at' => now(),
            ]);

            return $customer;
        });

        return redirect()
            ->route('masterdata.customers.show', $customer)
            ->with('success', "Prospect {$prospect->name} converted into customer {$customer->code}. Complete the legal and commercial details.");
    }

    private function validated(Request $request, ?Prospect $prospect = null): array
    {
        $rules = [
            'code' => ['nullable', 'string', 'max:20', Rule::unique('prospects', 'code')->ignore($prospect)],
            'type' => ['required', Rule::in(array_keys(Prospect::TYPES))],
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',

            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',

            'source' => ['required', Rule::in(array_keys(Prospect::SOURCES))],
            'status' => ['required', Rule::in(array_keys(Prospect::STATUSES))],
            'estimated_value' => 'nullable|numeric|min:0',
            'currency' => ['required', Rule::in(Prospect::CURRENCIES)],
            'probability' => 'required|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'next_follow_up_date' => 'nullable|date',
            'assigned_to' => 'nullable|string|max:100',
            'lost_reason' => 'nullable|required_if:status,lost|string|max:255',
            'notes' => 'nullable|string|max:5000',
        ];

        if (! $prospect) {
            $rules += Contact::quickRules();
        }

        $data = $request->validate($rules, [
            'lost_reason.required_if' => 'Please give a reason when the prospect is lost.',
        ], Contact::quickAttributes());

        unset($data['primary_contact']);
        $data['country'] = $data['country'] ?: 'Morocco';
        $data['estimated_value'] = $data['estimated_value'] ?? 0;
        if ($data['status'] !== 'lost') {
            $data['lost_reason'] = null;
        }

        return $data;
    }
}

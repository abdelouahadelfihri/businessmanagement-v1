<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::query()
            ->with('baseUnit')
            ->withCount(['products', 'derivedUnits'])
            ->search($request->input('search'))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->input('type')))
            ->when($request->input('status') === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderByRaw("FIELD(type, 'count', 'weight', 'volume', 'length', 'area', 'time')")
            ->orderByRaw('base_unit_id IS NOT NULL')
            ->orderBy('conversion_factor')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $typeCounts = Unit::selectRaw('type, COUNT(*) as total')->groupBy('type')->pluck('total', 'type');
        $isEmpty = ! Unit::exists();

        return view('masterdata.units.index', compact('units', 'typeCounts', 'isEmpty'));
    }

    public function create(Request $request)
    {
        $unit = new Unit([
            'type' => $request->query('type', 'count'),
            'base_unit_id' => $request->query('base_unit_id'),
            'conversion_factor' => 1,
            'decimal_places' => 0,
            'is_active' => true,
        ]);
        if ($unit->base_unit_id && ($base = Unit::find($unit->base_unit_id))) {
            $unit->type = $base->type;
        }

        return view('masterdata.units.create', compact('unit') + $this->formData());
    }

    public function store(Request $request)
    {
        $unit = Unit::create($this->validated($request));

        return redirect()->route('masterdata.units.show', $unit)->with('success', "Unit {$unit->name} created.");
    }

    public function show(Unit $unit)
    {
        $unit->load(['baseUnit', 'derivedUnits'])->loadCount('products');

        // Every other unit of the same family, with the equivalent of 1 of this unit
        $root = $unit->rootUnit();
        $family = Unit::with('baseUnit')
            ->where('type', $unit->type)
            ->whereKeyNot($unit->unit_id)
            ->get()
            ->filter(fn(Unit $u) => $u->rootUnit()->unit_id === $root->unit_id)
            ->map(fn(Unit $u) => ['unit' => $u, 'value' => $unit->convert(1, $u)])
            ->sortBy('value')
            ->values();

        $products = $unit->products()->with('category')->orderBy('name')->limit(15)->get();

        return view('masterdata.units.show', compact('unit', 'family', 'products'));
    }

    public function edit(Unit $unit)
    {
        return view('masterdata.units.edit', compact('unit') + $this->formData($unit));
    }

    public function update(Request $request, Unit $unit)
    {
        $unit->update($this->validated($request, $unit));

        return redirect()->route('masterdata.units.show', $unit)->with('success', "Unit {$unit->name} updated.");
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->exists()) {
            return back()->with('error', "{$unit->name} is used by products. Change their unit first, or mark this unit as inactive.");
        }
        if ($unit->derivedUnits()->exists()) {
            return back()->with('error', "Other units are defined from {$unit->name}. Change or delete them first.");
        }

        $unit->delete();

        return redirect()->route('masterdata.units.index')->with('success', "Unit {$unit->name} deleted.");
    }

    /** Adds the standard units that do not exist yet (matched by abbreviation). */
    public function defaults()
    {
        $created = 0;

        DB::transaction(function () use (&$created) {
            foreach (Unit::STANDARD as [$name, $abbr, $type, $baseAbbr, $factor, $decimals]) {
                if (Unit::where('abbreviation', $abbr)->orWhere('name', $name)->exists()) {
                    continue;
                }

                $base = $baseAbbr ? Unit::where('abbreviation', $baseAbbr)->first() : null;

                Unit::create([
                    'name' => $name,
                    'abbreviation' => $abbr,
                    'type' => $type,
                    'base_unit_id' => $base?->unit_id,
                    'conversion_factor' => $base ? $factor : 1,
                    'decimal_places' => $decimals,
                    'is_active' => true,
                ]);
                $created++;
            }
        });

        return redirect()->route('masterdata.units.index')
            ->with('success', $created ? "$created standard units added." : 'All standard units already exist.');
    }

    /** Quick create from the unit picker modal (returns JSON). */
    public function ajaxStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:units,name',
            'abbreviation' => 'nullable|string|max:20|unique:units,abbreviation',
        ]);

        return response()->json(Unit::create($data)->fresh());
    }

    /* ------------------------------------------------------------------ */

    private function formData(?Unit $unit = null): array
    {
        $excluded = $unit ? $unit->descendantIds() : [];

        return [
            'baseUnits' => Unit::whereNotIn('unit_id', $excluded)->orderBy('type')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Unit $unit = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('units', 'name')->ignore($unit?->unit_id, 'unit_id')],
            'abbreviation' => ['nullable', 'string', 'max:20', Rule::unique('units', 'abbreviation')->ignore($unit?->unit_id, 'unit_id')],
            'type' => ['required', Rule::in(array_keys(Unit::TYPES))],
            'base_unit_id' => ['nullable', 'exists:units,unit_id', Rule::notIn($unit ? $unit->descendantIds() : [])],
            'conversion_factor' => 'nullable|required_with:base_unit_id|numeric|gt:0|max:1000000000',
            'decimal_places' => 'required|integer|min:0|max:6',
            'description' => 'nullable|string|max:255',
        ], [
            'base_unit_id.not_in' => 'A unit cannot be based on itself or on a unit derived from it.',
            'conversion_factor.required_with' => 'Enter how many base units one of this unit equals.',
        ]);

        if (! empty($data['base_unit_id'])) {
            $base = Unit::find($data['base_unit_id']);
            if ($base->type !== $data['type']) {
                throw ValidationException::withMessages([
                    'base_unit_id' => "The base unit must be of the same type ({$base->type_label} ≠ " . Unit::TYPES[$data['type']]['label'] . ').',
                ]);
            }
        } else {
            $data['base_unit_id'] = null;
            $data['conversion_factor'] = 1;
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

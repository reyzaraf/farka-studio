<?php

namespace App\Http\Controllers\Admin\Calc;

use App\Http\Controllers\Controller;
use App\Models\Calc\FactorGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactorGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_calculator')->only(['index']);
        $this->middleware('permission:create_calculator')->only(['create', 'store']);
        $this->middleware('permission:edit_calculator')->only(['edit', 'update']);
        $this->middleware('permission:delete_calculator')->only(['destroy']);
    }

    public function index()
    {
        $factorGroups = FactorGroup::withCount('options')->orderBy('order')->get();
        return view('admin.calc.factor-groups.index', compact('factorGroups'));
    }

    public function create()
    {
        return view('admin.calc.factor-groups.form');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $group = FactorGroup::create([
            'key' => $data['key'],
            'name' => $data['name'],
            'order' => (FactorGroup::max('order') ?? 0) + 1,
        ]);
        $this->syncOptions($group, $data['options'], $request->input('default_index'));

        return redirect()->route('admin.calc.factor-groups.index')->with('success', 'Factor group created successfully.');
    }

    public function edit(string $id)
    {
        $group = FactorGroup::with('options')->findOrFail($id);
        return view('admin.calc.factor-groups.form', compact('group'));
    }

    public function update(Request $request, string $id)
    {
        $group = FactorGroup::findOrFail($id);
        $data = $this->validateData($request, (int) $id);
        $group->update(['key' => $data['key'], 'name' => $data['name']]);
        $this->syncOptions($group, $data['options'], $request->input('default_index'));

        return redirect()->route('admin.calc.factor-groups.index')->with('success', 'Factor group updated successfully.');
    }

    public function destroy(string $id)
    {
        FactorGroup::findOrFail($id)->delete(); // calc_factor_options cascade
        return redirect()->route('admin.calc.factor-groups.index')->with('success', 'Factor group deleted successfully.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'key' => 'required|string|max:100|unique:calc_factor_groups,key' . ($id ? ",$id" : ''),
            'name' => 'required|string|max:255',
            'options' => 'required|array|min:1',
            'options.*.label' => 'required|string|max:255',
            'options.*.multiplier' => 'required|numeric|min:0',
            'options.*.note' => 'nullable|string|max:255',
            'options.*.is_default' => 'nullable|boolean',
        ]);
    }

    /**
     * Options carry no external FKs, so the simplest correct sync is to delete and
     * recreate every submitted row. Exactly one option per group is default, driven
     * by the single `default_index` radio (falls back to the first row when unset).
     */
    private function syncOptions(FactorGroup $group, array $options, $defaultIndex): void
    {
        $options = array_values($options);
        $defaultIndex = (is_numeric($defaultIndex) && isset($options[(int) $defaultIndex])) ? (int) $defaultIndex : 0;

        DB::transaction(function () use ($group, $options, $defaultIndex) {
            $group->options()->delete();

            foreach ($options as $i => $opt) {
                $group->options()->create([
                    'label' => $opt['label'],
                    'multiplier' => $opt['multiplier'],
                    'note' => $opt['note'] ?? null,
                    'is_default' => $i === $defaultIndex,
                    'order' => $i,
                ]);
            }
        });
    }
}

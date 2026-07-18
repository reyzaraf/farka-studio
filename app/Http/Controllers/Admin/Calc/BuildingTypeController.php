<?php

namespace App\Http\Controllers\Admin\Calc;

use App\Http\Controllers\Controller;
use App\Models\Calc\BuildingType;
use Illuminate\Http\Request;

class BuildingTypeController extends Controller
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
        $buildingTypes = BuildingType::orderBy('order')->get();
        return view('admin.calc.building-types.index', compact('buildingTypes'));
    }

    public function create()
    {
        return view('admin.calc.building-types.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        BuildingType::create($data + ['order' => (BuildingType::max('order') ?? 0) + 1]);
        return redirect()->route('admin.calc.building-types.index')->with('success', 'Building type created successfully.');
    }

    public function edit(string $id)
    {
        $buildingType = BuildingType::findOrFail($id);
        return view('admin.calc.building-types.form', compact('buildingType'));
    }

    public function update(Request $request, string $id)
    {
        $buildingType = BuildingType::findOrFail($id);
        $data = $this->validated($request, (int) $id);
        $buildingType->update($data);
        return redirect()->route('admin.calc.building-types.index')->with('success', 'Building type updated successfully.');
    }

    public function destroy(string $id)
    {
        BuildingType::findOrFail($id)->delete();
        return redirect()->route('admin.calc.building-types.index')->with('success', 'Building type deleted successfully.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'key' => 'required|string|max:50|unique:calc_building_types,key' . ($id ? ",$id" : ''),
            'name' => 'required|string|max:255',
            'price_per_m2' => 'required|integer|min:0',
        ]);
    }
}

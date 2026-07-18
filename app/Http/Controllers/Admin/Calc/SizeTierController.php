<?php

namespace App\Http\Controllers\Admin\Calc;

use App\Http\Controllers\Controller;
use App\Models\Calc\SizeTier;
use Illuminate\Http\Request;

class SizeTierController extends Controller
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
        $sizeTiers = SizeTier::orderBy('order')->get();
        return view('admin.calc.size-tiers.index', compact('sizeTiers'));
    }

    public function create()
    {
        return view('admin.calc.size-tiers.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        SizeTier::create($data + ['order' => (SizeTier::max('order') ?? 0) + 1]);
        return redirect()->route('admin.calc.size-tiers.index')->with('success', 'Size tier created successfully.');
    }

    public function edit(string $id)
    {
        $sizeTier = SizeTier::findOrFail($id);
        return view('admin.calc.size-tiers.form', compact('sizeTier'));
    }

    public function update(Request $request, string $id)
    {
        $sizeTier = SizeTier::findOrFail($id);
        $data = $this->validated($request, (int) $id);
        $sizeTier->update($data);
        return redirect()->route('admin.calc.size-tiers.index')->with('success', 'Size tier updated successfully.');
    }

    public function destroy(string $id)
    {
        SizeTier::findOrFail($id)->delete();
        return redirect()->route('admin.calc.size-tiers.index')->with('success', 'Size tier deleted successfully.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'key' => 'required|string|max:50|unique:calc_size_tiers,key' . ($id ? ",$id" : ''),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin\Calc;

use App\Http\Controllers\Controller;
use App\Models\Calc\Allocation;
use Illuminate\Http\Request;

class AllocationController extends Controller
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
        $allocations = Allocation::orderBy('order')->get();
        return view('admin.calc.allocations.index', compact('allocations'));
    }

    public function create()
    {
        return view('admin.calc.allocations.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Allocation::create($this->normalize($data, $request) + ['order' => (Allocation::max('order') ?? 0) + 1]);
        return redirect()->route('admin.calc.allocations.index')->with('success', 'Allocation created successfully.');
    }

    public function edit(string $id)
    {
        $allocation = Allocation::findOrFail($id);
        return view('admin.calc.allocations.form', compact('allocation'));
    }

    public function update(Request $request, string $id)
    {
        $allocation = Allocation::findOrFail($id);
        $data = $this->validated($request);
        $allocation->update($this->normalize($data, $request));
        return redirect()->route('admin.calc.allocations.index')->with('success', 'Allocation updated successfully.');
    }

    public function destroy(string $id)
    {
        Allocation::findOrFail($id)->delete();
        return redirect()->route('admin.calc.allocations.index')->with('success', 'Allocation deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category' => 'required|in:pelaksanaan,perancangan,persiapan',
            'label' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0',
            'is_base' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'note' => 'nullable|string|max:255',
        ]);
    }

    private function normalize(array $data, Request $request): array
    {
        $data['percentage'] /= 100;
        $data['is_base'] = $request->boolean('is_base');
        $data['is_default'] = $request->boolean('is_default');
        return $data;
    }
}

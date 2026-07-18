<?php

namespace App\Http\Controllers\Admin\Calc;

use App\Http\Controllers\Controller;
use App\Models\Calc\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
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
        $components = Component::orderBy('order')->get();
        return view('admin.calc.components.index', compact('components'));
    }

    public function create()
    {
        return view('admin.calc.components.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Component::create($data + ['order' => (Component::max('order') ?? 0) + 1]);
        return redirect()->route('admin.calc.components.index')->with('success', 'Component created successfully.');
    }

    public function edit(string $id)
    {
        $component = Component::findOrFail($id);
        return view('admin.calc.components.form', compact('component'));
    }

    public function update(Request $request, string $id)
    {
        $component = Component::findOrFail($id);
        $data = $this->validated($request);
        $component->update($data);
        return redirect()->route('admin.calc.components.index')->with('success', 'Component updated successfully.');
    }

    public function destroy(string $id)
    {
        Component::findOrFail($id)->delete();
        return redirect()->route('admin.calc.components.index')->with('success', 'Component deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'standar' => 'required|string|max:500',
            'optimal' => 'required|string|max:500',
            'premium' => 'required|string|max:500',
        ]);
    }
}

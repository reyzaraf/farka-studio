<?php

namespace App\Http\Controllers\Admin\Calc;

use App\Http\Controllers\Controller;
use App\Models\Calc\Zonasi;
use Illuminate\Http\Request;

class ZonasiController extends Controller
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
        $zonasis = Zonasi::orderBy('order')->get();
        return view('admin.calc.zonasi.index', compact('zonasis'));
    }

    public function create()
    {
        return view('admin.calc.zonasi.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Zonasi::create($this->normalize($data) + ['order' => (Zonasi::max('order') ?? 0) + 1]);
        return redirect()->route('admin.calc.zonasi.index')->with('success', 'Zonasi created successfully.');
    }

    public function edit(string $id)
    {
        $zonasi = Zonasi::findOrFail($id);
        return view('admin.calc.zonasi.form', compact('zonasi'));
    }

    public function update(Request $request, string $id)
    {
        $zonasi = Zonasi::findOrFail($id);
        $data = $this->validated($request, (int) $id);
        $zonasi->update($this->normalize($data));
        return redirect()->route('admin.calc.zonasi.index')->with('success', 'Zonasi updated successfully.');
    }

    public function destroy(string $id)
    {
        Zonasi::findOrFail($id)->delete();
        return redirect()->route('admin.calc.zonasi.index')->with('success', 'Zonasi deleted successfully.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:50|unique:calc_zonasi,code' . ($id ? ",$id" : ''),
            'name' => 'nullable|string|max:255',
            'kdb' => 'required|numeric|min:0|max:100',
            'klb' => 'required|numeric|min:0',
            'ktb' => 'required|numeric|min:0|max:100',
            'rth' => 'required|numeric|min:0|max:100',
        ]);
    }

    private function normalize(array $data): array
    {
        $data['kdb'] /= 100;
        $data['ktb'] /= 100;
        $data['rth'] /= 100;
        return $data;
    }
}

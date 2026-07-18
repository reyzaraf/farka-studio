<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalculateBudgetRequest;
use App\Models\Calc\Allocation;
use App\Models\Calc\BuildingType;
use App\Models\Calc\Component;
use App\Models\Calc\FactorGroup;
use App\Models\Calc\Room;
use App\Models\Calc\Setting;
use App\Models\Calc\SizeTier;
use App\Models\Calc\Zonasi;
use App\Services\BudgetCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;

class BudgetCalculatorController extends Controller
{
    public function show()
    {
        return view('kalkulator.show', $this->referenceData());
    }

    public function calculate(CalculateBudgetRequest $request, BudgetCalculatorService $service)
    {
        return response()->json($service->calculate($request->calculatorInput()));
    }

    public function pdf(CalculateBudgetRequest $request, BudgetCalculatorService $service)
    {
        $input = $request->calculatorInput();
        $result = $service->calculate($input);
        $name = $input['nama_proyek'] !== '' ? $input['nama_proyek'] : 'Proyek';
        $filename = 'Estimasi-Budget-' . \Illuminate\Support\Str::slug($name) . '.pdf';

        return Pdf::loadView('kalkulator.pdf', [
            'input' => $input,
            'result' => $result,
            'buildingType' => BuildingType::find($input['building_type_id']),
            'zonasi' => Zonasi::find($input['zonasi_id']),
            'allocations' => Allocation::whereIn('id', $input['allocation_ids'] ?? [])
                ->orderBy('order')->get()->groupBy('category'),
            'generatedAt' => now()->format('d F Y'),
        ])->setPaper('a4')->download($filename);
    }

    /** Shared reference data for the form (Task 8 reuses this for the PDF). */
    private function referenceData(): array
    {
        return [
            'factorGroups' => FactorGroup::with('options')->orderBy('order')->get(),
            'allocations' => Allocation::orderBy('order')->get()->groupBy('category'),
            'buildingTypes' => BuildingType::orderBy('order')->get(),
            'components' => Component::orderBy('order')->get(),
            'zonasiList' => Zonasi::orderBy('order')->get(),
            'sizeTiers' => SizeTier::orderBy('order')->get(),
            'rooms' => Room::with('areas')->orderBy('category')->orderBy('order')->get(),
            'settings' => [
                'dana_darurat_pct' => (float) Setting::value('dana_darurat_pct', 0.10),
                'sirkulasi_pct' => (float) Setting::value('sirkulasi_pct', 0.20),
                'toleransi_default' => (float) Setting::value('toleransi_default', 0),
            ],
        ];
    }
}

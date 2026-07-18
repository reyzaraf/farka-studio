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

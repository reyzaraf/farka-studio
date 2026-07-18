<?php

namespace App\Services;

use App\Models\Calc\Allocation;
use App\Models\Calc\BuildingType;
use App\Models\Calc\FactorOption;
use App\Models\Calc\Room;
use App\Models\Calc\RoomArea;
use App\Models\Calc\SizeTier;
use App\Models\Calc\Zonasi;

class BudgetCalculatorService
{
    /**
     * Compute the full budget estimate. Money is rounded to whole rupiah;
     * areas keep full precision (round only for display).
     */
    public function calculate(array $in): array
    {
        // --- Weighting ---
        $options = FactorOption::whereIn('id', $in['factor_option_ids'] ?? [])->get();
        $bobot = 1.0;
        $factors = [];
        foreach ($options as $opt) {
            $bobot *= (float) $opt->multiplier;
            $factors[] = ['label' => $opt->label, 'multiplier' => (float) $opt->multiplier];
        }

        $basePrice = (int) BuildingType::findOrFail($in['building_type_id'])->price_per_m2;
        $hargaBobot = (int) round($basePrice * $bobot);

        // --- Budget chain ---
        $gross = (int) round($in['budget'] + $in['toleransi']);
        $danaDarurat = (int) round($gross * $in['dana_darurat_pct']);
        $nett = $gross - $danaDarurat;

        // total_alokasi = sum of selected NON-base allocation percentages.
        $totalAlokasi = (float) Allocation::whereIn('id', $in['allocation_ids'] ?? [])
            ->where('is_base', false)
            ->sum('percentage');
        $nettConstruction = (int) round($nett / (1 + $totalAlokasi));

        $budgetArea = $hargaBobot > 0 ? $nettConstruction / $hargaBobot : 0.0;

        // --- Regulation ---
        $zonasi = Zonasi::findOrFail($in['zonasi_id']);
        $land = (float) $in['luas_tanah'];
        $kdb = $zonasi->kdb * $land;
        $klb = $zonasi->klb * $land;
        $ktb = $zonasi->ktb * $land;
        $rth = $zonasi->rth * $land;
        $luasTerbangun = $klb;
        $regulasiCost = (int) round($luasTerbangun * $hargaBobot);

        // --- Needs ---
        $roomsIn = $in['rooms'] ?? [];
        $roomIds = collect($roomsIn)->pluck('room_id')->unique()->values();
        $tierIds = collect($roomsIn)->pluck('size_tier_id')->unique()->values();
        $roomNames = Room::whereIn('id', $roomIds)->pluck('name', 'id');
        $tierNames = SizeTier::whereIn('id', $tierIds)->pluck('name', 'id');
        $areas = RoomArea::whereIn('room_id', $roomIds)
            ->whereIn('size_tier_id', $tierIds)
            ->get()
            ->keyBy(fn ($a) => $a->room_id.'-'.$a->size_tier_id);

        $rows = [];
        $subtotals = ['utama' => 0.0, 'sekunder' => 0.0, 'tersier' => 0.0];
        foreach ($roomsIn as $r) {
            $areaUnit = (float) ($areas[$r['room_id'].'-'.$r['size_tier_id']]->area ?? 0);
            $qty = (int) $r['jumlah'];
            $total = $areaUnit * $qty;
            $prio = $r['prioritas'];
            if (isset($subtotals[$prio])) {
                $subtotals[$prio] += $total;
            }
            $rows[] = [
                'name' => $roomNames[$r['room_id']] ?? '',
                'prioritas' => $prio,
                'jumlah' => $qty,
                'tier' => $tierNames[$r['size_tier_id']] ?? '',
                'area_unit' => $areaUnit,
                'total' => $total,
            ];
        }
        $roomsTotal = $subtotals['utama'] + $subtotals['sekunder'] + $subtotals['tersier'];
        $sirkulasiPct = (float) $in['sirkulasi_pct'];
        $sirkulasi = $roomsTotal * $sirkulasiPct;
        $grandTotal = $roomsTotal * (1 + $sirkulasiPct);

        // --- Summary ---
        $baseline = $nettConstruction;
        $mult = 1 + $sirkulasiPct;
        $utamaArea = $subtotals['utama'] * $mult;
        $sekunderArea = ($subtotals['utama'] + $subtotals['sekunder']) * $mult;
        $tersierArea = ($subtotals['utama'] + $subtotals['sekunder'] + $subtotals['tersier']) * $mult;

        $cost = fn (float $area) => (int) round($area * $hargaBobot);
        $costUtama = $cost($utamaArea);
        $costSekunder = $cost($sekunderArea);
        $costTersier = $cost($tersierArea);
        $summaryRows = [
            ['label' => 'Budget', 'area' => $budgetArea, 'cost' => $baseline, 'selisih' => null],
            ['label' => 'Regulation', 'area' => $luasTerbangun, 'cost' => $regulasiCost, 'selisih' => $baseline - $regulasiCost],
            ['label' => 'Needs (Primary)', 'area' => $utamaArea, 'cost' => $costUtama, 'selisih' => $baseline - $costUtama],
            ['label' => 'Needs (+Secondary)', 'area' => $sekunderArea, 'cost' => $costSekunder, 'selisih' => $baseline - $costSekunder],
            ['label' => 'Needs (+Tertiary)', 'area' => $tersierArea, 'cost' => $costTersier, 'selisih' => $baseline - $costTersier],
        ];

        return [
            'weighting' => [
                'bobot' => $bobot,
                'base_price' => $basePrice,
                'harga_per_m2_bobot' => $hargaBobot,
                'factors' => $factors,
            ],
            'budget' => [
                'gross' => $gross,
                'dana_darurat' => $danaDarurat,
                'nett' => $nett,
                'total_alokasi' => $totalAlokasi,
                'nett_construction' => $nettConstruction,
                'area' => $budgetArea,
            ],
            'regulation' => [
                'code' => $zonasi->code,
                'kdb' => $kdb, 'klb' => $klb, 'ktb' => $ktb, 'rth' => $rth,
                'luas_terbangun' => $luasTerbangun,
                'cost' => $regulasiCost,
            ],
            'needs' => [
                'rows' => $rows,
                'subtotals' => $subtotals,
                'rooms_total' => $roomsTotal,
                'sirkulasi' => $sirkulasi,
                'grand_total' => $grandTotal,
            ],
            'summary' => [
                'baseline' => $baseline,
                'rows' => $summaryRows,
            ],
        ];
    }
}

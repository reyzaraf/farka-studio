<?php

namespace Database\Seeders;

use App\Models\Calc\Allocation;
use App\Models\Calc\BuildingType;
use App\Models\Calc\Component;
use App\Models\Calc\FactorGroup;
use App\Models\Calc\FactorOption;
use App\Models\Calc\Room;
use App\Models\Calc\RoomArea;
use App\Models\Calc\Setting;
use App\Models\Calc\SizeTier;
use App\Models\Calc\Zonasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BudgetCalculatorSeeder extends Seeder
{
    public function run(): void
    {
        $json = json_decode(file_get_contents(database_path('data/budget_calculator_seed.json')), true);
        $t = $json['tables'];

        // Size tiers first — rooms reference them by name.
        foreach ($t['size_tiers'] as $i => $row) {
            SizeTier::updateOrCreate(
                ['key' => $row['key']],
                ['name' => $row['name'], 'description' => $row['description'] ?? null, 'order' => $i + 1]
            );
        }
        $tierByName = SizeTier::pluck('id', 'name'); // 'Minimum' => id ...

        // Factor groups + options.
        foreach ($t['factor_groups'] as $gi => $g) {
            $group = FactorGroup::updateOrCreate(
                ['key' => $g['key']],
                ['name' => $g['name'], 'order' => $gi + 1]
            );
            foreach ($g['options'] as $oi => $opt) {
                FactorOption::updateOrCreate(
                    ['factor_group_id' => $group->id, 'label' => $opt['label']],
                    [
                        'multiplier' => $opt['multiplier'],
                        'note' => $opt['note'] ?? null,
                        'is_default' => $opt['is_default'] ?? false,
                        'order' => $oi + 1,
                    ]
                );
            }
        }

        foreach ($t['allocations'] as $i => $a) {
            Allocation::updateOrCreate(
                ['category' => $a['category'], 'label' => $a['label']],
                [
                    'percentage' => $a['percentage'],
                    'is_base' => $a['is_base'] ?? false,
                    'is_default' => $a['is_default'] ?? false,
                    'order' => $i + 1,
                ]
            );
        }

        foreach ($t['building_types'] as $i => $b) {
            BuildingType::updateOrCreate(
                ['key' => $b['key']],
                ['name' => $b['name'], 'price_per_m2' => $b['price_per_m2'], 'order' => $i + 1]
            );
        }

        foreach ($t['components'] as $i => $c) {
            Component::updateOrCreate(
                ['name' => $c['name']],
                ['standar' => $c['standar'], 'optimal' => $c['optimal'], 'premium' => $c['premium'], 'order' => $i + 1]
            );
        }

        foreach ($t['zonasi'] as $i => $z) {
            Zonasi::updateOrCreate(
                ['code' => $z['code']],
                [
                    'name' => $z['name'] ?? null,
                    'kdb' => $z['kdb'], 'klb' => $z['klb'], 'ktb' => $z['ktb'], 'rth' => $z['rth'],
                    'order' => $i + 1,
                ]
            );
        }

        foreach ($t['settings'] as $s) {
            Setting::updateOrCreate(
                ['key' => $s['key']],
                ['value' => $s['value'], 'note' => $s['note'] ?? null]
            );
        }

        // Rooms + areas. area = panjang * lebar (full precision), NOT the JSON display value.
        foreach ($json['rooms'] as $ri => $r) {
            $room = Room::updateOrCreate(
                ['name' => $r['name'], 'category' => Str::lower($r['category'])],
                ['order' => $ri + 1]
            );
            foreach ($r['areas'] as $a) {
                $tierId = $tierByName[$a['tier']] ?? null;
                if (!$tierId) {
                    continue;
                }
                $panjang = $a['p'] ?? 0;
                $lebar = $a['l'] ?? 0;
                RoomArea::updateOrCreate(
                    ['room_id' => $room->id, 'size_tier_id' => $tierId],
                    ['panjang' => $panjang, 'lebar' => $lebar, 'area' => round($panjang * $lebar, 2)]
                );
            }
        }
    }
}

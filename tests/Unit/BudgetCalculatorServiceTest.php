<?php

namespace Tests\Unit;

use App\Models\Calc\Allocation;
use App\Models\Calc\BuildingType;
use App\Models\Calc\FactorOption;
use App\Models\Calc\Room;
use App\Models\Calc\SizeTier;
use App\Models\Calc\Zonasi;
use App\Services\BudgetCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    private function optionId(string $groupKey, string $label): int
    {
        return FactorOption::where('label', $label)
            ->whereHas('group', fn ($q) => $q->where('key', $groupKey))
            ->value('id');
    }

    private function sheetExampleInput(): array
    {
        $premium = SizeTier::where('key', 'premium')->value('id');
        $roomId = fn (string $name, string $cat) => Room::where('name', $name)->where('category', $cat)->value('id');

        // The 21-room program from the sheet (all Premium tier).
        $program = [
            ['Dapur kotor', 'service', 1, 'utama'],
            ['Area cuci', 'service', 1, 'utama'],
            ['Gudang', 'service', 1, 'utama'],
            ['Area utilitas', 'service', 1, 'utama'],
            ['Kamar tidur supir', 'service', 1, 'utama'],
            ['Kamar mandi supir', 'service', 1, 'utama'],
            ['Carport', 'public', 2, 'utama'],
            ['Garasi', 'public', 2, 'utama'],
            ['Gerbang utama', 'public', 1, 'utama'],
            ['Ruang tamu', 'public', 1, 'utama'],
            ['Ruang makan', 'public', 1, 'utama'],
            ['Dapur', 'public', 1, 'utama'],
            ['Toilet tamu', 'public', 2, 'utama'],
            ['Taman depan', 'public', 1, 'utama'],
            ['Kamar tidur utama', 'private', 1, 'utama'],
            ['Walk-in-closet', 'private', 1, 'utama'],
            ['Kamar mandi utama', 'private', 1, 'utama'],
            ['Kamar tidur', 'private', 2, 'utama'],
            ['Kamar mandi', 'private', 1, 'utama'],
            ['Ruang kerja', 'private', 1, 'sekunder'],
            ['Foyer', 'public', 1, 'tersier'],
        ];
        $rooms = [];
        foreach ($program as [$name, $cat, $qty, $prio]) {
            $rooms[] = [
                'room_id' => $roomId($name, $cat),
                'size_tier_id' => $premium,
                'jumlah' => $qty,
                'prioritas' => $prio,
            ];
        }

        // Default extra allocations: Landscape, Biaya arsitek, Pembersihan lahan, Tes tanah, Survey topografi.
        $allocIds = Allocation::where('is_default', true)->where('is_base', false)->pluck('id')->all();

        return [
            'nama_proyek' => 'Rizal',
            'luas_tanah' => 300,
            'lokasi_proyek' => 'Jakarta',
            'factor_option_ids' => [
                $this->optionId('jabodetabek', 'Ya'),
                $this->optionId('existing_building', 'Tidak'),
                $this->optionId('target_building', 'Bangun baru'),
                $this->optionId('style', 'Mediterranean'),
            ],
            'building_type_id' => BuildingType::where('key', 'standar')->value('id'),
            'zonasi_id' => Zonasi::where('code', 'R-3')->value('id'),
            'budget' => 2_000_000_000,
            'toleransi' => 0,
            'dana_darurat_pct' => 0.10,
            'sirkulasi_pct' => 0.20,
            'allocation_ids' => $allocIds,
            'rooms' => $rooms,
        ];
    }

    public function test_reproduces_sheet_example(): void
    {
        $result = (new BudgetCalculatorService())->calculate($this->sheetExampleInput());

        // Weighting
        $this->assertEqualsWithDelta(1.15, $result['weighting']['bobot'], 0.0001);
        $this->assertSame(5_500_000, $result['weighting']['base_price']);
        $this->assertSame(6_325_000, $result['weighting']['harga_per_m2_bobot']);

        // Budget chain
        $this->assertSame(2_000_000_000, $result['budget']['gross']);
        $this->assertSame(200_000_000, $result['budget']['dana_darurat']);
        $this->assertSame(1_800_000_000, $result['budget']['nett']);
        $this->assertEqualsWithDelta(0.20, $result['budget']['total_alokasi'], 0.0001);
        $this->assertSame(1_500_000_000, $result['budget']['nett_construction']);
        $this->assertEqualsWithDelta(237.2, round($result['budget']['area'], 1), 0.05);

        // Regulation (R-3, land 300)
        $this->assertEqualsWithDelta(180.0, $result['regulation']['kdb'], 0.05);
        $this->assertEqualsWithDelta(360.0, $result['regulation']['klb'], 0.05);
        $this->assertEqualsWithDelta(120.0, $result['regulation']['rth'], 0.05);
        $this->assertEqualsWithDelta(360.0, $result['regulation']['luas_terbangun'], 0.05);
        $this->assertSame(2_277_000_000, $result['regulation']['cost']);

        // Needs
        $this->assertEqualsWithDelta(379.75, $result['needs']['subtotals']['utama'], 0.01);
        $this->assertEqualsWithDelta(20.0, $result['needs']['subtotals']['sekunder'], 0.01);
        $this->assertEqualsWithDelta(12.0, $result['needs']['subtotals']['tersier'], 0.01);
        $this->assertEqualsWithDelta(411.75, $result['needs']['rooms_total'], 0.01);
        $this->assertEqualsWithDelta(494.1, round($result['needs']['grand_total'], 1), 0.05);

        // Summary rows: [Budget, Regulasi, Kebutuhan Utama, +Sekunder, +Tersier]
        $rows = $result['summary']['rows'];
        $this->assertSame(1_500_000_000, $result['summary']['baseline']);
        $this->assertSame(1_500_000_000, $rows[0]['cost']);       // Budget baseline
        $this->assertNull($rows[0]['selisih']);
        $this->assertSame(2_277_000_000, $rows[1]['cost']);       // Regulasi
        $this->assertSame(-777_000_000, $rows[1]['selisih']);
        $this->assertSame(2_882_302_500, $rows[2]['cost']);       // Kebutuhan Utama
        $this->assertSame(-1_382_302_500, $rows[2]['selisih']);
        $this->assertSame(3_034_102_500, $rows[3]['cost']);       // +Sekunder
        $this->assertSame(3_125_182_500, $rows[4]['cost']);       // +Tersier
    }
}

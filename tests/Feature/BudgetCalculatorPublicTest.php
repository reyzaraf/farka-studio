<?php

namespace Tests\Feature;

use App\Models\Calc\BuildingType;
use App\Models\Calc\FactorOption;
use App\Models\Calc\Zonasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCalculatorPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_public_page_renders_without_auth(): void
    {
        $this->get(route('kalkulator.show'))->assertOk()->assertViewIs('kalkulator.show');
    }

    public function test_calculate_endpoint_returns_expected_numbers(): void
    {
        $opt = fn (string $g, string $l) => FactorOption::where('label', $l)
            ->whereHas('group', fn ($q) => $q->where('key', $g))->value('id');

        $payload = [
            'nama_proyek' => 'Rizal',
            'luas_tanah' => 300,
            'factor_option_ids' => [
                $opt('jabodetabek', 'Ya'), $opt('existing_building', 'Tidak'),
                $opt('target_building', 'Bangun baru'), $opt('style', 'Mediterranean'),
            ],
            'building_type_id' => BuildingType::where('key', 'standar')->value('id'),
            'zonasi_id' => Zonasi::where('code', 'R-3')->value('id'),
            'budget' => 2_000_000_000,
            'toleransi' => 0,
            'dana_darurat_pct' => 0.10,
            'sirkulasi_pct' => 0.20,
            'allocation_ids' => \App\Models\Calc\Allocation::where('is_default', true)->where('is_base', false)->pluck('id')->all(),
            'rooms' => [],
        ];

        $res = $this->postJson(route('kalkulator.calculate'), $payload);
        $res->assertOk()
            ->assertJsonPath('weighting.harga_per_m2_bobot', 6_325_000)
            ->assertJsonPath('budget.nett_construction', 1_500_000_000)
            ->assertJsonPath('regulation.cost', 2_277_000_000);
    }

    public function test_calculate_applies_setting_defaults_when_percentages_omitted(): void
    {
        $opt = fn (string $g, string $l) => \App\Models\Calc\FactorOption::where('label', $l)
            ->whereHas('group', fn ($q) => $q->where('key', $g))->value('id');

        // Same as the sheet example but WITHOUT dana_darurat_pct / sirkulasi_pct.
        // The seeded defaults (0.10 / 0.20) must be applied, yielding nett_construction 1.5B.
        $payload = [
            'nama_proyek' => 'Rizal',
            'luas_tanah' => 300,
            'factor_option_ids' => [
                $opt('jabodetabek', 'Ya'), $opt('existing_building', 'Tidak'),
                $opt('target_building', 'Bangun baru'), $opt('style', 'Mediterranean'),
            ],
            'building_type_id' => \App\Models\Calc\BuildingType::where('key', 'standar')->value('id'),
            'zonasi_id' => \App\Models\Calc\Zonasi::where('code', 'R-3')->value('id'),
            'budget' => 2_000_000_000,
            'toleransi' => 0,
            'allocation_ids' => \App\Models\Calc\Allocation::where('is_default', true)->where('is_base', false)->pluck('id')->all(),
            'rooms' => [],
        ];

        $this->postJson(route('kalkulator.calculate'), $payload)
            ->assertOk()
            ->assertJsonPath('budget.nett_construction', 1_500_000_000);
    }

    public function test_calculate_validates_input(): void
    {
        $this->postJson(route('kalkulator.calculate'), [])->assertStatus(422);
    }
}

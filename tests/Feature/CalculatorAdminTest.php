<?php

namespace Tests\Feature;

use App\Models\Calc\Room;
use App\Models\Calc\RoomArea;
use App\Models\Calc\FactorOption;
use App\Models\Calc\Allocation;
use App\Models\Calc\Zonasi;
use App\Models\Calc\SizeTier;
use App\Models\Calc\BuildingType;
use App\Models\Calc\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_reference_data_is_seeded_completely(): void
    {
        $this->assertSame(63, Room::count());
        $this->assertSame(63 * 6, RoomArea::count());
        $this->assertSame(6, SizeTier::count());
        $this->assertSame(16, FactorOption::count());
        $this->assertSame(15, Allocation::count());
        $this->assertSame(5, Zonasi::count());
        $this->assertSame(3, BuildingType::count());
        $this->assertSame(10, Component::count());

        // Precision guard: Kamar mandi Premium must be 6.25 (2.5 x 2.5), not 6.3.
        $kamarMandi = Room::where('name', 'Kamar mandi')->where('category', 'private')->first();
        $premium = SizeTier::where('key', 'premium')->first();
        $area = RoomArea::where('room_id', $kamarMandi->id)->where('size_tier_id', $premium->id)->first();
        $this->assertEqualsWithDelta(6.25, $area->area, 0.001);
    }
}

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
use App\Models\Calc\Setting;
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
        $this->assertSame(3, Setting::count());

        // Precision guard: Kamar mandi Premium must be 6.25 (2.5 x 2.5), not 6.3.
        $kamarMandi = Room::where('name', 'Kamar mandi')->where('category', 'private')->first();
        $premium = SizeTier::where('key', 'premium')->first();
        $area = RoomArea::where('room_id', $kamarMandi->id)->where('size_tier_id', $premium->id)->first();
        $this->assertEqualsWithDelta(6.25, $area->area, 0.001);
    }

    private function superAdmin(): \App\Models\User
    {
        return \App\Models\User::where('email', 'admin@farkastudio.test')->first();
    }

    public function test_admin_can_create_and_delete_room(): void
    {
        $admin = $this->superAdmin();
        $tiers = SizeTier::orderBy('order')->get();
        $areas = $tiers->values()->map(fn ($t, $i) => ['size_tier_id' => $t->id, 'panjang' => 2, 'lebar' => 3])->all();

        $this->actingAs($admin)->post(route('admin.calc.rooms.store'), [
            'category' => 'service', 'name' => 'Test Room', 'areas' => $areas,
        ])->assertRedirect(route('admin.calc.rooms.index'));

        $room = Room::where('name', 'Test Room')->first();
        $this->assertNotNull($room);
        $this->assertSame(6, $room->areas()->count());
        $this->assertEqualsWithDelta(6.0, $room->areas()->first()->area, 0.001); // 2*3

        $this->actingAs($admin)->delete(route('admin.calc.rooms.destroy', $room->id))
            ->assertRedirect(route('admin.calc.rooms.index'));
        $this->assertNull(Room::find($room->id));
        $this->assertSame(0, RoomArea::where('room_id', $room->id)->count());
    }

    public function test_room_index_renders(): void
    {
        $this->actingAs($this->superAdmin())->get(route('admin.calc.rooms.index'))->assertOk();
    }
}

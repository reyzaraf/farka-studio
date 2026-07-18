<?php

namespace App\Http\Controllers\Admin\Calc;

use App\Http\Controllers\Controller;
use App\Models\Calc\Room;
use App\Models\Calc\SizeTier;
use Illuminate\Http\Request;

class RoomController extends Controller
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
        $rooms = Room::withCount('areas')->orderBy('category')->orderBy('order')->get();
        return view('admin.calc.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $sizeTiers = SizeTier::orderBy('order')->get();
        return view('admin.calc.rooms.form', compact('sizeTiers'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $room = Room::create([
            'category' => $data['category'],
            'name' => $data['name'],
            'order' => (Room::max('order') ?? 0) + 1,
        ]);
        $this->syncAreas($room, $data['areas']);

        return redirect()->route('admin.calc.rooms.index')->with('success', 'Room created successfully.');
    }

    public function edit(string $id)
    {
        $room = Room::with('areas')->findOrFail($id);
        $sizeTiers = SizeTier::orderBy('order')->get();
        $areasByTier = $room->areas->keyBy('size_tier_id');
        return view('admin.calc.rooms.form', compact('room', 'sizeTiers', 'areasByTier'));
    }

    public function update(Request $request, string $id)
    {
        $room = Room::findOrFail($id);
        $data = $this->validateData($request);
        $room->update(['category' => $data['category'], 'name' => $data['name']]);
        $this->syncAreas($room, $data['areas']);

        return redirect()->route('admin.calc.rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(string $id)
    {
        Room::findOrFail($id)->delete(); // room_areas cascade
        return redirect()->route('admin.calc.rooms.index')->with('success', 'Room deleted successfully.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'category' => 'required|in:service,public,private,luxury',
            'name' => 'required|string|max:255',
            'areas' => 'required|array',
            'areas.*.size_tier_id' => 'required|integer|exists:calc_size_tiers,id',
            'areas.*.panjang' => 'required|numeric|min:0',
            'areas.*.lebar' => 'required|numeric|min:0',
        ]);
    }

    /** area = panjang * lebar (full precision). */
    private function syncAreas(Room $room, array $areas): void
    {
        foreach ($areas as $a) {
            $room->areas()->updateOrCreate(
                ['size_tier_id' => $a['size_tier_id']],
                ['panjang' => $a['panjang'], 'lebar' => $a['lebar'], 'area' => round($a['panjang'] * $a['lebar'], 2)]
            );
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeyPerson;
use Illuminate\Http\Request;

class KeyPersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $people = KeyPerson::orderBy('order')->get();
        return view('admin.key-people.index', compact('people'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $nextOrder = (KeyPerson::max('order') ?? 0) + 1;
        return view('admin.key-people.form', compact('nextOrder'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image_url' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image_url')) {
            $validated['image_url'] = collect([\Illuminate\Support\Facades\Storage::disk('public')->put('key-people', $request->file('image_url'))])->first();
        }

        KeyPerson::create($validated);

        return redirect()->route('admin.key-people.index')->with('success', 'Team member created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // View not strictly required
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $person = KeyPerson::findOrFail($id);
        return view('admin.key-people.form', compact('person'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $person = KeyPerson::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image_url' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image_url')) {
            if ($person->image_url) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($person->image_url);
            }
            $validated['image_url'] = collect([\Illuminate\Support\Facades\Storage::disk('public')->put('key-people', $request->file('image_url'))])->first();
        }

        $person->update($validated);

        return redirect()->route('admin.key-people.index')->with('success', 'Team member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $person = KeyPerson::findOrFail($id);
        if ($person->image_url) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($person->image_url);
        }
        $person->delete();
        
        return redirect()->route('admin.key-people.index')->with('success', 'Team member deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_categories')->only(['index']);
        $this->middleware('permission:create_categories')->only(['create', 'store']);
        $this->middleware('permission:edit_categories')->only(['edit', 'update', 'reorder']);
        $this->middleware('permission:delete_categories')->only(['destroy', 'bulkDestroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::withCount('projects')->orderBy('order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
        ]);

        // New categories go to the end; operators reorder by dragging on the list.
        $validated['order'] = (Category::max('order') ?? 0) + 1;

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not necessary for this admin panel right now, list view + form is enough.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.form', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $id,
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        
        // Ensure no projects are silently orphaned or deleted restrictively depending on DB setup.
        // Assuming cascade or set null is handled in DB. Let's just delete the category.
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Delete multiple categories at once.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:categories,id',
        ]);

        $count = Category::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', $count . ' category(ies) deleted successfully.');
    }

    /**
     * Persist a new display order from drag-and-drop on the list.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:categories,id',
        ]);

        foreach ($validated['ids'] as $position => $id) {
            Category::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['status' => 'ok']);
    }
}

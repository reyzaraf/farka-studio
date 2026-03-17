<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Category;
use App\Models\KeyPerson;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with(['category', 'contents'])->latest()->get();
        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $architects = KeyPerson::pluck('name');
        return view('admin.projects.form', compact('categories', 'architects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:projects,slug',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|string|max:255',
            'architect' => 'nullable|string|max:255',
            'floor_area' => 'nullable|string|max:255',
            'site_area' => 'nullable|string|max:255',
            'stories' => 'nullable|string|max:255',
            'location' => 'nullable|string',
            'contents.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg,gif|max:5120',
            'contents.*.description' => 'nullable|string',
            'contents.*.order' => 'nullable|integer',
        ]);

        $project = Project::create($validated);

        if ($request->has('contents')) {
            foreach ($request->file('contents', []) as $index => $fileData) {
                $contentData = $request->input("contents.$index");
                if (isset($fileData['image'])) {
                    $path = $fileData['image']->store('project-contents', 'public');
                    $project->contents()->create([
                        'image_url' => $path,
                        'description' => $contentData['description'] ?? null,
                        'order' => $contentData['order'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not used
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $project = Project::findOrFail($id);
        $categories = Category::all();
        $architects = KeyPerson::pluck('name');
        return view('admin.projects.form', compact('project', 'categories', 'architects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:projects,slug,' . $id,
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|string|max:255',
            'architect' => 'nullable|string|max:255',
            'floor_area' => 'nullable|string|max:255',
            'site_area' => 'nullable|string|max:255',
            'stories' => 'nullable|string|max:255',
            'location' => 'nullable|string',
            'contents.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg,gif|max:5120',
            'contents.*.description' => 'nullable|string',
            'contents.*.order' => 'nullable|integer',
        ]);

        $project->update($validated);

        // Handle deletions
        if ($request->has('deleted_contents')) {
            $contentsToDelete = $project->contents()->whereIn('id', $request->deleted_contents)->get();
            foreach ($contentsToDelete as $content) {
                if ($content->image_url) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($content->image_url);
                }
                $content->delete();
            }
        }

        // Handle updates and new items
        if ($request->has('contents')) {
            $files = $request->file('contents') ?? [];
            $inputs = $request->input('contents') ?? [];

            foreach ($inputs as $index => $contentData) {
                // If it exists, update it
                if (isset($contentData['id'])) {
                    $content = $project->contents()->find($contentData['id']);
                    if ($content) {
                        $updateData = [
                            'description' => $contentData['description'] ?? null,
                            'order' => $contentData['order'] ?? 0,
                        ];
                        
                        if (isset($files[$index]['image'])) {
                            if ($content->image_url) {
                                \Illuminate\Support\Facades\Storage::disk('public')->delete($content->image_url);
                            }
                            $updateData['image_url'] = $files[$index]['image']->store('project-contents', 'public');
                        }
                        
                        $content->update($updateData);
                    }
                } else {
                    // New item
                    if (isset($files[$index]['image'])) {
                        $path = $files[$index]['image']->store('project-contents', 'public');
                        $project->contents()->create([
                            'image_url' => $path,
                            'description' => $contentData['description'] ?? null,
                            'order' => $contentData['order'] ?? 0,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        
        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }
}

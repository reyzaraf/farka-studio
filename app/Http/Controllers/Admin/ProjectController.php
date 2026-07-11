<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Category;
use App\Models\KeyPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_projects')->only(['index']);
        $this->middleware('permission:create_projects')->only(['create', 'store']);
        $this->middleware('permission:edit_projects')->only(['edit', 'update', 'reorder']);
        $this->middleware('permission:delete_projects')->only(['destroy', 'bulkDestroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with(['category', 'contents'])->orderBy('order', 'asc')->latest()->get();
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
        $validated = $request->validate($this->rules());
        $validated['is_published'] = $request->boolean('is_published');

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

        $validated = $request->validate($this->rules($id));
        $validated['is_published'] = $request->boolean('is_published');

        $project->update($validated);

        // Handle deletions
        if ($request->has('deleted_contents')) {
            $contentsToDelete = $project->contents()->whereIn('id', $request->deleted_contents)->get();
            foreach ($contentsToDelete as $content) {
                if ($content->image_url) {
                    Storage::disk('public')->delete($content->image_url);
                }
                $content->delete();
            }
        }

        // Handle updates and new items
        if ($request->has('contents')) {
            $files = $request->file('contents') ?? [];
            $inputs = $request->input('contents') ?? [];

            foreach ($inputs as $index => $contentData) {
                if (isset($contentData['id'])) {
                    $content = $project->contents()->find($contentData['id']);
                    if ($content) {
                        $updateData = [
                            'description' => $contentData['description'] ?? null,
                            'order' => $contentData['order'] ?? 0,
                        ];

                        if (isset($files[$index]['image'])) {
                            if ($content->image_url) {
                                Storage::disk('public')->delete($content->image_url);
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
        $project = Project::with('contents')->findOrFail($id);
        $this->deleteProjectImages($project);
        $project->delete(); // project_contents rows cascade at the DB level

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }

    /**
     * Delete multiple projects at once.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:projects,id',
        ]);

        $projects = Project::with('contents')->whereIn('id', $validated['ids'])->get();
        foreach ($projects as $project) {
            $this->deleteProjectImages($project);
            $project->delete();
        }

        return redirect()->route('admin.projects.index')
            ->with('success', $projects->count() . ' project(s) deleted successfully.');
    }

    /**
     * Persist a new display order from drag-and-drop on the list.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:projects,id',
        ]);

        foreach ($validated['ids'] as $position => $id) {
            Project::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Shared validation rules for store/update.
     */
    private function rules(?string $ignoreId = null): array
    {
        $slugUnique = 'required|string|max:255|unique:projects,slug' . ($ignoreId ? ',' . $ignoreId : '');

        return [
            'title' => 'required|string|max:255',
            'slug' => $slugUnique,
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|string|max:255',
            'architect' => 'nullable|string|max:255',
            'floor_area' => 'nullable|string|max:255',
            'site_area' => 'nullable|string|max:255',
            'stories' => 'nullable|string|max:255',
            'location' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_published' => 'nullable|boolean',
            'contents.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg,gif|max:35840',
            'contents.*.description' => 'nullable|string',
            'contents.*.order' => 'nullable|integer',
        ];
    }

    private function deleteProjectImages(Project $project): void
    {
        foreach ($project->contents as $content) {
            if ($content->image_url) {
                Storage::disk('public')->delete($content->image_url);
            }
        }
    }
}

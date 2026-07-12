<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ContactSetting;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function index()
    {
        $projectsQuery = Project::with('contents')->where('is_published', true)->orderBy('order', 'asc')->get();
        $projects = $projectsQuery->keyBy('slug');
        
        $projects_json = $projectsQuery->mapWithKeys(function ($project) {
            return [$project->slug => [
                'title' => $project->title,
                'status' => $project->status,
                'architect' => $project->architect,
                'floor' => $project->floor_area,
                'site' => $project->site_area,
                'stories' => $project->stories,
                'location' => $project->location,
                'category' => $project->category ? $project->category->name : 'N/A',
                'content' => $project->contents->map(function ($content) {
                    return [
                        'img' => asset('storage/' . $content->image_url),
                        'text' => $content->description,
                    ];
                })->values()
            ]];
        })->toJson();

        $contact = ContactSetting::first() ?? new ContactSetting();
        $categories = \App\Models\Category::orderBy('order')->get();
        $keyPeople = \App\Models\KeyPerson::orderBy('order')->get();

        return view('index', compact('projects', 'projects_json', 'contact', 'categories', 'keyPeople'));
    }
}

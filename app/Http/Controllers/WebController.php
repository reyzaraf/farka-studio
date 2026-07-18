<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ContactSetting;
use Illuminate\Support\Str;

class WebController extends Controller
{
    public function index()
    {
        return view('index', $this->siteData());
    }

    /**
     * Crawlable per-project URL: same single-page app, but with project-specific
     * meta tags, JSON-LD, a hidden SEO content block, and a signal for the SPA to
     * auto-open this project on load.
     */
    public function show(string $slug)
    {
        $project = Project::with('contents')
            ->where('is_published', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $desc = $project->stories
            ?: ($project->title . ' — proyek arsitektur & desain interior oleh Farka Studio'
                . ($project->location ? ' di ' . $project->location : '') . '.');
        $desc = Str::limit(trim(strip_tags($desc)), 160);

        $firstImage = optional($project->contents->first())->image_url;

        return view('index', array_merge($this->siteData(), [
            'metaTitle'         => $project->title . ' | Farka Studio',
            'metaDescription'   => $desc,
            'metaImage'         => $firstImage ? asset('storage/' . $firstImage) : asset('farkalogo.svg'),
            'activeProject'     => $project,
            'activeProjectSlug' => $project->slug,
        ]));
    }

    public function about()
    {
        return $this->staticPage('about');
    }

    public function contact()
    {
        return $this->staticPage('contact');
    }

    /**
     * Crawlable deep-link for a static SPA section (about / contact): renders the
     * single-page app with section-specific meta and a signal to auto-open it.
     */
    private function staticPage(string $page)
    {
        $contact = ContactSetting::first();

        $meta = [
            'about' => [
                'title' => 'About | Farka Studio',
                'desc'  => ($contact && $contact->about_description)
                    ? $contact->about_description
                    : 'Farka Studio adalah biro arsitektur dan desain interior yang berpusat di Indonesia — menciptakan ruang yang bernapas, berdialog dengan lingkungan, dan berdampak positif bagi kehidupan penggunanya.',
            ],
            'contact' => [
                'title' => 'Contact | Farka Studio',
                'desc'  => 'Hubungi Farka Studio — email, telepon/WhatsApp, dan alamat studio kami untuk konsultasi proyek arsitektur & desain interior Anda.',
            ],
        ][$page];

        return view('index', array_merge($this->siteData(), [
            'metaTitle'       => $meta['title'],
            'metaDescription' => Str::limit(trim(strip_tags($meta['desc'])), 160),
            'activePage'      => $page,
        ]));
    }

    /**
     * Shared data for the single-page site (used by both the homepage and the
     * per-project deep-link route).
     */
    private function siteData(): array
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
                })->values(),
            ]];
        })->toJson();

        $contact = ContactSetting::first() ?? new ContactSetting();
        $categories = \App\Models\Category::orderBy('order')->get();
        $keyPeople = \App\Models\KeyPerson::orderBy('order')->get();

        return compact('projects', 'projects_json', 'contact', 'categories', 'keyPeople');
    }
}

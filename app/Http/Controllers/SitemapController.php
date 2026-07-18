<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function index()
    {
        // Homepage lastmod tracks the most recently updated published project.
        $latest = Project::where('is_published', true)->max('updated_at');
        $homeLastmod = ($latest ? Carbon::parse($latest) : now())->toAtomString();

        $urls = [
            [
                'loc'        => url('/'),
                'lastmod'    => $homeLastmod,
                'changefreq' => 'weekly',
                'priority'   => '1.0',
            ],
            [
                'loc'        => route('kalkulator.show'),
                'lastmod'    => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority'   => '0.8',
            ],
            [
                'loc'        => route('about'),
                'lastmod'    => now()->toAtomString(),
                'changefreq' => 'yearly',
                'priority'   => '0.6',
            ],
            [
                'loc'        => route('contact'),
                'lastmod'    => now()->toAtomString(),
                'changefreq' => 'yearly',
                'priority'   => '0.6',
            ],
        ];

        // One crawlable URL per published project.
        foreach (Project::where('is_published', true)->orderBy('order')->get(['slug', 'updated_at']) as $p) {
            $urls[] = [
                'loc'        => route('project.show', $p->slug),
                'lastmod'    => ($p->updated_at ?? now())->toAtomString(),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class SitemapController extends Controller
{
    public function index()
    {
        $latestProject = Project::latest('updated_at')->first();
        $lastmod = $latestProject ? $latestProject->updated_at->toAtomString() : now()->toAtomString();

        return response()->view('sitemap', [
            'lastmod' => $lastmod
        ])->header('Content-Type', 'text/xml');
    }
}

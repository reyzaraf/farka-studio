<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use App\Models\KeyPerson;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'projects' => Project::count(),
            'categories' => Category::count(),
            'users' => User::count(),
            'key_people' => KeyPerson::count(),
        ];

        $recentProjects = Project::with('category')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentProjects'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\KeyPerson;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global admin quick-search across the content types the user may view.
     */
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();
        $like = '%' . $q . '%';
        $results = [];

        if ($user->can('view_projects')) {
            foreach (Project::where('title', 'like', $like)->orderBy('title')->limit(6)->get() as $p) {
                $results[] = [
                    'type'     => 'Project',
                    'icon'     => 'ph-briefcase',
                    'label'    => $p->title,
                    'sublabel' => $p->is_published ? 'Published' : 'Draft',
                    'url'      => route('admin.projects.edit', $p->id),
                ];
            }
        }

        if ($user->can('view_key_people')) {
            $people = KeyPerson::where(function ($query) use ($like) {
                $query->where('name', 'like', $like)->orWhere('role', 'like', $like);
            })->orderBy('name')->limit(5)->get();
            foreach ($people as $k) {
                $results[] = [
                    'type'     => 'Team',
                    'icon'     => 'ph-identification-badge',
                    'label'    => $k->name,
                    'sublabel' => $k->role,
                    'url'      => route('admin.key-people.edit', $k->id),
                ];
            }
        }

        if ($user->can('view_categories')) {
            foreach (Category::where('name', 'like', $like)->orderBy('order')->limit(5)->get() as $c) {
                $results[] = [
                    'type'     => 'Category',
                    'icon'     => 'ph-squares-four',
                    'label'    => $c->name,
                    'sublabel' => $c->slug,
                    'url'      => route('admin.categories.edit', $c->id),
                ];
            }
        }

        if ($user->hasRole('super_admin')) {
            $users = User::where(function ($query) use ($like) {
                $query->where('name', 'like', $like)->orWhere('email', 'like', $like);
            })->orderBy('name')->limit(5)->get();
            foreach ($users as $u) {
                $results[] = [
                    'type'     => 'User',
                    'icon'     => 'ph-user-circle',
                    'label'    => $u->name,
                    'sublabel' => $u->email,
                    'url'      => route('admin.users.edit', $u->id),
                ];
            }
        }

        return response()->json(['results' => $results, 'query' => $q]);
    }
}

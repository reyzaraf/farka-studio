<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\KeyPerson;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    private function superAdmin(): User
    {
        return User::where('email', 'admin@farkastudio.test')->first();
    }

    private function editor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('editor');
        return $user;
    }

    /* ---------------- Admin pages render (compiles every touched Blade view) ---------------- */

    public function test_admin_pages_render_for_super_admin(): void
    {
        $category = Category::create(['name' => 'Residential', 'slug' => 'residential']);
        $project = Project::create(['title' => 'Villa X', 'slug' => 'villa-x', 'order' => 1]);
        $person = KeyPerson::create(['name' => 'Jane', 'role' => 'Architect', 'order' => 1]);
        $editorRole = Role::where('name', 'editor')->first();

        $admin = $this->superAdmin();

        foreach ([
            route('admin.dashboard'),
            route('admin.projects.index'),
            route('admin.projects.create'),
            route('admin.projects.edit', $project->id),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.categories.edit', $category->id),
            route('admin.key-people.index'),
            route('admin.key-people.create'),
            route('admin.key-people.edit', $person->id),
            route('admin.contact-settings.edit'),
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.edit', $admin->id),
            route('admin.roles.index'),
            route('admin.roles.create'),
            route('admin.roles.edit', $editorRole->id),
            route('admin.profile.edit'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login to Admin Portal');
    }

    /* ---------------- #10 Draft / published visibility ---------------- */

    public function test_public_site_hides_unpublished_projects(): void
    {
        Project::create(['title' => 'ZZPublishedProject', 'slug' => 'live', 'order' => 1, 'is_published' => true]);
        Project::create(['title' => 'ZZDraftProject', 'slug' => 'draft', 'order' => 2, 'is_published' => false]);

        $this->get('/')->assertOk()->assertSee('ZZPublishedProject')->assertDontSee('ZZDraftProject');
    }

    public function test_store_respects_publish_checkbox(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.projects.store'), [
            'title' => 'Published One', 'slug' => 'published-one',
        ])->assertRedirect(route('admin.projects.index'));
        // checkbox omitted while other fields present => draft
        $this->assertFalse(Project::where('slug', 'published-one')->first()->is_published);

        $this->actingAs($admin)->post(route('admin.projects.store'), [
            'title' => 'Published Two', 'slug' => 'published-two', 'is_published' => '1',
        ]);
        $this->assertTrue(Project::where('slug', 'published-two')->first()->is_published);

        // The form's hidden "0" input submits an explicit false when the switch is off.
        $this->actingAs($admin)->post(route('admin.projects.store'), [
            'title' => 'Explicit Draft', 'slug' => 'explicit-draft', 'is_published' => '0',
        ]);
        $this->assertFalse(Project::where('slug', 'explicit-draft')->first()->is_published);
    }

    public function test_projects_show_route_does_not_500(): void
    {
        $project = Project::create(['title' => 'X', 'slug' => 'x', 'order' => 1]);
        // show() was removed and the route excluded; GET on the {project} URI (which still exists
        // for PUT/DELETE) must be a clean 405 Method Not Allowed, never the old 500.
        $this->actingAs($this->superAdmin())->get('/admin/projects/' . $project->id)->assertStatus(405);
    }

    /* ---------------- #17 Role enforcement + registration ---------------- */

    public function test_editor_can_access_content_but_not_users(): void
    {
        $editor = $this->editor();
        $this->actingAs($editor)->get(route('admin.projects.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_super_admin_bypasses_permission_checks(): void
    {
        // super_admin content access works even though enforcement middleware is present
        $this->actingAs($this->superAdmin())->get(route('admin.projects.index'))->assertOk();
        $this->actingAs($this->superAdmin())->get(route('admin.users.index'))->assertOk();
    }

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    /* ---------------- #15 Self-service profile ---------------- */

    public function test_user_can_change_own_password_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
        $user->assignRole('editor');

        $this->actingAs($user)->put(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect(route('admin.profile.edit'));

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_password_change_rejected_with_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
        $user->assignRole('editor');

        $this->actingAs($user)->put(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'WRONG',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('oldpassword', $user->fresh()->password));
    }

    /* ---------------- #13 Bulk delete + protections ---------------- */

    public function test_bulk_delete_projects(): void
    {
        $a = Project::create(['title' => 'A', 'slug' => 'a', 'order' => 1]);
        $b = Project::create(['title' => 'B', 'slug' => 'b', 'order' => 2]);
        $c = Project::create(['title' => 'C', 'slug' => 'c', 'order' => 3]);

        $this->actingAs($this->superAdmin())
            ->delete(route('admin.projects.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertRedirect(route('admin.projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $a->id]);
        $this->assertDatabaseMissing('projects', ['id' => $b->id]);
        $this->assertDatabaseHas('projects', ['id' => $c->id]);
    }

    public function test_bulk_delete_users_never_deletes_self(): void
    {
        $admin = $this->superAdmin();
        $other = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.bulk-destroy'), ['ids' => [$admin->id, $other->id]]);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);      // self preserved
        $this->assertDatabaseMissing('users', ['id' => $other->id]);  // other deleted
    }

    public function test_bulk_delete_roles_never_deletes_super_admin(): void
    {
        $superRole = Role::where('name', 'super_admin')->first();
        $editorRole = Role::where('name', 'editor')->first();

        $this->actingAs($this->superAdmin())
            ->delete(route('admin.roles.bulk-destroy'), ['ids' => [$superRole->id, $editorRole->id]]);

        $this->assertDatabaseHas('roles', ['id' => $superRole->id]);      // protected
        $this->assertDatabaseMissing('roles', ['id' => $editorRole->id]); // deleted
    }

    /* ---------------- #12 Drag reorder (projects list) ---------------- */

    public function test_project_reorder_persists_new_order(): void
    {
        $a = Project::create(['title' => 'A', 'slug' => 'a', 'order' => 1]);
        $b = Project::create(['title' => 'B', 'slug' => 'b', 'order' => 2]);
        $c = Project::create(['title' => 'C', 'slug' => 'c', 'order' => 3]);

        // Drag into order: C, A, B
        $this->actingAs($this->superAdmin())
            ->post(route('admin.projects.reorder'), ['ids' => [$c->id, $a->id, $b->id]])
            ->assertOk()->assertJson(['status' => 'ok']);

        $this->assertEquals(1, $c->fresh()->order);
        $this->assertEquals(2, $a->fresh()->order);
        $this->assertEquals(3, $b->fresh()->order);
    }

    public function test_project_reorder_requires_edit_permission(): void
    {
        $viewer = User::factory()->create();
        $role = Role::create(['name' => 'viewer']);
        $role->givePermissionTo('view_projects'); // no edit_projects
        $viewer->assignRole($role);

        $p = Project::create(['title' => 'A', 'slug' => 'a', 'order' => 1]);

        $this->actingAs($viewer)
            ->post(route('admin.projects.reorder'), ['ids' => [$p->id]])
            ->assertForbidden();
    }

    /* ---------------- #11 Media rows preserved on validation failure ---------------- */

    public function test_validation_error_repopulates_media_descriptions(): void
    {
        $admin = $this->superAdmin();

        // Missing title => validation fails; contents description should be re-rendered from old()
        $response = $this->actingAs($admin)->from(route('admin.projects.create'))->post(route('admin.projects.store'), [
            'slug' => 'no-title',
            'contents' => [
                ['description' => 'My caption survives', 'order' => 1],
            ],
        ]);

        $response->assertRedirect(route('admin.projects.create'));
        $this->followRedirects($response)->assertSee('My caption survives');
    }
}

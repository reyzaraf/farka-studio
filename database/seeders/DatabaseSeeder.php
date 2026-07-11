<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
        ]);

        // Super admin — also given every permission explicitly (Gate::before already
        // grants a bypass, but this keeps the roles UI accurate).
        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
        $superAdminRole->syncPermissions(\Spatie\Permission\Models\Permission::all());

        // Editor — day-to-day content staff: manage portfolio content, but no user/role admin.
        $editorRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'editor']);
        $editorRole->syncPermissions([
            'view_projects', 'create_projects', 'edit_projects', 'delete_projects',
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            'view_key_people', 'create_key_people', 'edit_key_people', 'delete_key_people',
            'view_page_settings', 'edit_page_settings',
        ]);

        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@farkastudio.test'],
            [
                'name' => 'Super Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        $admin->assignRole($superAdminRole);
    }
}

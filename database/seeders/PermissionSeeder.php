<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view_projects', 'create_projects', 'edit_projects', 'delete_projects',
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            'view_key_people', 'create_key_people', 'edit_key_people', 'delete_key_people',
            'view_page_settings', 'edit_page_settings',
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }
    }
}

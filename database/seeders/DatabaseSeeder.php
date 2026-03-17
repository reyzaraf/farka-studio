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

        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

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

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Ensure Spatie roles exist
        Role::firstOrCreate(['name' => 'librarian']);
        Role::firstOrCreate(['name' => 'assistant']);

        // 2. Create or find the admin user
        $admin = User::firstOrCreate(
            ['id' => 1], // Fixed database anchor
            [
                'username' => 'admin',
                'role'     => 'librarian',
                'password' => Hash::make('Admin2026'),
            ]
        );

        // 3. Explicitly assign the Spatie role to guarantee the pivot record exists
        if (!$admin->hasRole('librarian')) {
            $admin->assignRole('librarian');
        }

        $this->call([
            PatronTypeSeeder::class,
        ]);
    }
}

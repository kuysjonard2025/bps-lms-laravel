<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Safe creation: Only creates the user if 'role' => 'admin' does not exist
        User::firstOrCreate(
            ['role' => 'admin'], // Search criteria: Ensures only 1 admin ever exists
            [
                'first_name'        => null,
                'middle_name'       => null,
                'last_name'         => null,
                'prefix'            => null,
                'address'           => null,
                'contact_number'    => null,
                'email'             => null,
                'email_verified_at' => null,
                'username'          => 'admin', // Required for login!
                'password'          => Hash::make('Admin2026'),
            ]
        );

        // Optional: Generate 10 random fake users using the updated UserFactory
        // User::factory(10)->create();
    }
}

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
        // Create the Admin / Default User matching your schema
        User::factory()->create([
            'first_name' => null,
            'middle_name' => null,
            'last_name' => null,
            'prefix' => null,
            'address' => null,
            'contact_number' => null,
            'email' => null,
            'email_verified_at' => null,
            'username' => 'admin',
            'password' => Hash::make('Admin2026'),
            'role' => 'admin',
        ]);

        // Optional: Generate 10 random fake users using the updated UserFactory
        // User::factory(10)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\PatronType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatronTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patronTypes = [
            [
                'name' => 'Student',
                // 'description' => 'Enrolled undergraduate or postgraduate students',
            ],
            [
                'name' => 'Faculty',
                // 'description' => 'Academic teaching staff and professors',
            ],
            [
                'name' => 'Staff',
                // 'description' => 'Administrative and support personnel',
            ],
            [
                'name' => 'Guest / Visitor',
                // 'description' => 'External patrons or temporary cardholders',
            ],
        ];

        foreach ($patronTypes as $type) {
            PatronType::updateOrCreate(
                ['name' => $type['name']],
                // ['description' => $type['description']]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\TrialClass;
use Illuminate\Database\Seeder;

class TrialClassSeeder extends Seeder
{
    public function run(): void
    {
        // Class with available seats (capacity 4, 0 confirmed)
        TrialClass::factory()->create([
            'title' => 'Math Trial - 10:00',
            'start_at' => now()->addDays(1)->setTime(10, 0),
            'capacity' => 4,
        ]);

        // Class with exactly 3 confirmed students (capacity 4, 3 confirmed = 1 seat left)
        TrialClass::factory()->create([
            'title' => 'Science Trial - 14:00',
            'start_at' => now()->addDays(1)->setTime(14, 0),
            'capacity' => 4,
        ]);

        // Class at full capacity (4 confirmed)
        TrialClass::factory()->create([
            'title' => 'English Trial - 16:00',
            'start_at' => now()->addDays(2)->setTime(16, 0),
            'capacity' => 4,
        ]);

        // Additional classes
        TrialClass::factory()->count(3)->create([
            'capacity' => 4,
        ]);
    }
}
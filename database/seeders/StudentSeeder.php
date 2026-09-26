<?php

namespace Database\Seeders;

use App\Models\ParentModel;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $parents = ParentModel::all();

        // Create known students for specific test scenarios
        $parent1 = $parents->firstWhere('email', 'john.doe@example.com');
        $parent2 = $parents->firstWhere('email', 'jane.smith@example.com');

        if ($parent1) {
            Student::factory()->create([
                'parent_id' => $parent1->id,
                'name' => 'Alice',
            ]);

            Student::factory()->create([
                'parent_id' => $parent1->id,
                'name' => 'Bob',
            ]);
        }

        if ($parent2) {
            Student::factory()->create([
                'parent_id' => $parent2->id,
                'name' => 'Charlie',
            ]);

            Student::factory()->create([
                'parent_id' => $parent2->id,
                'name' => 'David',
            ]);
        }

        // Create additional random students
        $parents->each(function ($parent) {
            Student::factory()->count(rand(1, 2))->create([
                'parent_id' => $parent->id,
            ]);
        });
    }
}
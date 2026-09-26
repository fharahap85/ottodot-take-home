<?php

namespace Database\Seeders;

use App\Models\ParentModel;
use Illuminate\Database\Seeder;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        ParentModel::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);

        ParentModel::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
        ]);

        ParentModel::factory()->count(3)->create();
    }
}
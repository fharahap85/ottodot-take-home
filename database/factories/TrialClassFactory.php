<?php

namespace Database\Factories;

use App\Models\TrialClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrialClassFactory extends Factory
{
    protected $model = TrialClass::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word() . ' Trial - ' . $this->faker->time('H:i'),
            'start_at' => $this->faker->dateTimeBetween('+1 day', '+7 days'),
            'capacity' => 4,
        ];
    }
}
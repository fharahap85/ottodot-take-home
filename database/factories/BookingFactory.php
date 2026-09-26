<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Student;
use App\Models\TrialClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'trial_class_id' => TrialClass::factory(),
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ];
    }
}
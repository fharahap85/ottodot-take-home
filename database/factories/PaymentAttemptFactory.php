<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\PaymentAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentAttemptFactory extends Factory
{
    protected $model = PaymentAttempt::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'status' => PaymentAttempt::STATUS_PENDING,
            'amount' => 1000,
            'paid_at' => null,
        ];
    }
}
<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\PaymentAttempt;
use App\Models\Student;
use App\Models\TrialClass;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $classes = TrialClass::all();

        $mathClass = $classes->firstWhere('title', 'Math Trial - 10:00');
        $scienceClass = $classes->firstWhere('title', 'Science Trial - 14:00');
        $englishClass = $classes->firstWhere('title', 'English Trial - 16:00');

        $alice = $students->firstWhere('name', 'Alice');
        $bob = $students->firstWhere('name', 'Bob');
        $charlie = $students->firstWhere('name', 'Charlie');
        $david = $students->firstWhere('name', 'David');

        // Scenario 1: Class with available seats (Math Trial - 0 confirmed)
        // No bookings for Math Trial - all 4 seats available

        // Scenario 2: Class with exactly 3 confirmed students (Science Trial)
        if ($scienceClass && $alice && $bob && $charlie) {
            $booking1 = Booking::factory()->create([
                'student_id' => $alice->id,
                'trial_class_id' => $scienceClass->id,
                'status' => Booking::STATUS_CONFIRMED,
            ]);
            PaymentAttempt::factory()->create([
                'booking_id' => $booking1->id,
                'status' => PaymentAttempt::STATUS_SUCCESS,
                'amount' => 1000,
                'paid_at' => now()->subHour(),
            ]);

            $booking2 = Booking::factory()->create([
                'student_id' => $bob->id,
                'trial_class_id' => $scienceClass->id,
                'status' => Booking::STATUS_CONFIRMED,
            ]);
            PaymentAttempt::factory()->create([
                'booking_id' => $booking2->id,
                'status' => PaymentAttempt::STATUS_SUCCESS,
                'amount' => 1000,
                'paid_at' => now()->subHour(),
            ]);

            $booking3 = Booking::factory()->create([
                'student_id' => $charlie->id,
                'trial_class_id' => $scienceClass->id,
                'status' => Booking::STATUS_CONFIRMED,
            ]);
            PaymentAttempt::factory()->create([
                'booking_id' => $booking3->id,
                'status' => PaymentAttempt::STATUS_SUCCESS,
                'amount' => 1000,
                'paid_at' => now()->subHour(),
            ]);
        }

        // Scenario 3: Class at full capacity (English Trial - 4 confirmed)
        if ($englishClass) {
            $englishStudents = $students->take(4);
            foreach ($englishStudents as $student) {
                $booking = Booking::factory()->create([
                    'student_id' => $student->id,
                    'trial_class_id' => $englishClass->id,
                    'status' => Booking::STATUS_CONFIRMED,
                ]);
                PaymentAttempt::factory()->create([
                    'booking_id' => $booking->id,
                    'status' => PaymentAttempt::STATUS_SUCCESS,
                    'amount' => 1000,
                    'paid_at' => now()->subHour(),
                ]);
            }
        }

        // Scenario 4: Duplicate booking attempt scenario
        // Create a pending booking for David on Science Trial (same class as Alice, Bob, Charlie)
        // This will be used to test duplicate confirmed booking protection
        if ($scienceClass && $david) {
            Booking::factory()->create([
                'student_id' => $david->id,
                'trial_class_id' => $scienceClass->id,
                'status' => Booking::STATUS_PENDING_PAYMENT,
            ]);
        }

        // Scenario 5: Failed payment scenario
        // Create a booking with failed payment
        if ($mathClass) {
            $failedStudent = $students->where('name', '!=', 'Alice')->where('name', '!=', 'Bob')->first();
            if ($failedStudent) {
                $failedBooking = Booking::factory()->create([
                    'student_id' => $failedStudent->id,
                    'trial_class_id' => $mathClass->id,
                    'status' => Booking::STATUS_PAYMENT_FAILED,
                ]);
                PaymentAttempt::factory()->create([
                    'booking_id' => $failedBooking->id,
                    'status' => PaymentAttempt::STATUS_FAILED,
                    'amount' => 1000,
                    'paid_at' => now()->subHour(),
                ]);
            }
        }

        // Additional random bookings
        $classes->each(function ($trialClass) use ($students, $mathClass, $scienceClass, $englishClass) {
            if ($trialClass->id !== $mathClass?->id && $trialClass->id !== $scienceClass?->id && $trialClass->id !== $englishClass?->id) {
                $randomStudents = $students->random(rand(0, 2));
                foreach ($randomStudents as $student) {
                    $status = fake()->randomElement([
                        Booking::STATUS_PENDING_PAYMENT,
                        Booking::STATUS_CONFIRMED,
                        Booking::STATUS_PAYMENT_FAILED,
                    ]);

                    $booking = Booking::factory()->create([
                        'student_id' => $student->id,
                        'trial_class_id' => $trialClass->id,
                        'status' => $status,
                    ]);

                    if ($status === Booking::STATUS_CONFIRMED || $status === Booking::STATUS_PAYMENT_FAILED) {
                        PaymentAttempt::factory()->create([
                            'booking_id' => $booking->id,
                            'status' => $status === Booking::STATUS_CONFIRMED ? PaymentAttempt::STATUS_SUCCESS : PaymentAttempt::STATUS_FAILED,
                            'amount' => 1000,
                            'paid_at' => now()->subHour(),
                        ]);
                    }
                }
            }
        });
    }
}
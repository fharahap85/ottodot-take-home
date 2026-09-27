<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\TrialClass;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Real concurrent verification test for last-seat competition using cURL Multi.
     * Fires two simultaneous HTTP POST requests against the running web server container.
     */
    public function test_real_concurrent_http_payment_requests_for_last_seat(): void
    {
        $parent = ParentModel::factory()->create();
        $trialClass = TrialClass::factory()->create(['capacity' => 1]);

        $studentA = Student::factory()->create(['parent_id' => $parent->id]);
        $studentB = Student::factory()->create(['parent_id' => $parent->id]);

        $bookingA = Booking::factory()->create([
            'student_id' => $studentA->id,
            'trial_class_id' => $trialClass->id,
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);

        $bookingB = Booking::factory()->create([
            'student_id' => $studentB->id,
            'trial_class_id' => $trialClass->id,
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);

        // Target Nginx server container or host URL
        $baseUrl = gethostbyname('ottodot-nginx') !== 'ottodot-nginx'
            ? 'http://ottodot-nginx'
            : 'http://localhost:8000';

        $mh = curl_multi_init();
        $handles = [];

        $bookings = [$bookingA, $bookingB];

        foreach ($bookings as $booking) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "{$baseUrl}/bookings/{$booking->id}/payment",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(['result' => 'success']),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'X-Requested-With: XMLHttpRequest',
                ],
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$booking->id] = $ch;
        }

        // Execute handles concurrently
        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh, 0.05);
        } while ($running > 0);

        foreach ($handles as $ch) {
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);

        $bookingA->refresh();
        $bookingB->refresh();

        $statuses = [$bookingA->status, $bookingB->status];
        sort($statuses);

        $this->assertEquals(['confirmed', 'payment_failed'], $statuses,
            'Real concurrent HTTP requests for the last seat must result in exactly 1 confirmed and 1 payment_failed.'
        );

        $confirmedCount = Booking::where('trial_class_id', $trialClass->id)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->count();

        $this->assertEquals(1, $confirmedCount,
            'Confirmed bookings count must equal capacity (1) and never exceed it.'
        );
    }
}

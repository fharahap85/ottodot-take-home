<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ParentModel;
use App\Models\PaymentAttempt;
use App\Models\Student;
use App\Models\TrialClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_trial_booking(): void
    {
        $parent = ParentModel::factory()->create();
        $student = Student::factory()->create(['parent_id' => $parent->id]);
        $trialClass = TrialClass::factory()->create(['capacity' => 4]);

        $response = $this->postJson('/bookings', [
            'student_id' => $student->id,
            'trial_class_id' => $trialClass->id,
        ]);

        $response->assertStatus(302); // Redirect to booking show

        $booking = Booking::latest()->first();
        $this->assertNotNull($booking);
        $this->assertEquals($student->id, $booking->student_id);
        $this->assertEquals($trialClass->id, $booking->trial_class_id);
        $this->assertEquals(Booking::STATUS_PENDING_PAYMENT, $booking->status);
    }

    public function test_successful_payment_confirms_booking_when_capacity_exists(): void
    {
        $parent = ParentModel::factory()->create();
        $student = Student::factory()->create(['parent_id' => $parent->id]);
        $trialClass = TrialClass::factory()->create(['capacity' => 4]);

        $booking = Booking::factory()->create([
            'student_id' => $student->id,
            'trial_class_id' => $trialClass->id,
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);

        $response = $this->postJson("/bookings/{$booking->id}/payment", [
            'result' => 'success',
        ]);

        $response->assertStatus(302);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);

        $paymentAttempt = PaymentAttempt::where('booking_id', $booking->id)->first();
        $this->assertNotNull($paymentAttempt);
        $this->assertEquals(PaymentAttempt::STATUS_SUCCESS, $paymentAttempt->status);
    }

    public function test_failed_payment_does_not_confirm_booking(): void
    {
        $parent = ParentModel::factory()->create();
        $student = Student::factory()->create(['parent_id' => $parent->id]);
        $trialClass = TrialClass::factory()->create(['capacity' => 4]);

        $booking = Booking::factory()->create([
            'student_id' => $student->id,
            'trial_class_id' => $trialClass->id,
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);

        $response = $this->postJson("/bookings/{$booking->id}/payment", [
            'result' => 'failed',
        ]);

        $response->assertStatus(302);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PAYMENT_FAILED, $booking->status);

        $paymentAttempt = PaymentAttempt::where('booking_id', $booking->id)->first();
        $this->assertNotNull($paymentAttempt);
        $this->assertEquals(PaymentAttempt::STATUS_FAILED, $paymentAttempt->status);
    }

    public function test_failed_payment_does_not_appear_in_confirmed_roster(): void
    {
        $parent = ParentModel::factory()->create();
        $student = Student::factory()->create(['parent_id' => $parent->id]);
        $trialClass = TrialClass::factory()->create(['capacity' => 4]);

        $booking = Booking::factory()->create([
            'student_id' => $student->id,
            'trial_class_id' => $trialClass->id,
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);

        $this->postJson("/bookings/{$booking->id}/payment", [
            'result' => 'failed',
        ]);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PAYMENT_FAILED, $booking->status);

        // Verify confirmed count is still 0
        $confirmedCount = Booking::where('trial_class_id', $trialClass->id)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->count();
        $this->assertEquals(0, $confirmedCount);
    }

    public function test_duplicate_confirmed_booking_is_prevented(): void
    {
        $parent = ParentModel::factory()->create();
        $student = Student::factory()->create(['parent_id' => $parent->id]);
        $trialClass = TrialClass::factory()->create(['capacity' => 4]);

        // Create first confirmed booking
        $booking1 = Booking::factory()->create([
            'student_id' => $student->id,
            'trial_class_id' => $trialClass->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        PaymentAttempt::factory()->create([
            'booking_id' => $booking1->id,
            'status' => PaymentAttempt::STATUS_SUCCESS,
        ]);

        // Try to create another booking for same student + class
        $response = $this->postJson('/bookings', [
            'student_id' => $student->id,
            'trial_class_id' => $trialClass->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['student_id']);
    }

    public function test_class_cannot_exceed_capacity(): void
    {
        $parent = ParentModel::factory()->create();
        $trialClass = TrialClass::factory()->create(['capacity' => 4]);

        // Create 4 confirmed bookings
        for ($i = 0; $i < 4; $i++) {
            $student = Student::factory()->create(['parent_id' => $parent->id]);
            $booking = Booking::factory()->create([
                'student_id' => $student->id,
                'trial_class_id' => $trialClass->id,
                'status' => Booking::STATUS_CONFIRMED,
            ]);
            PaymentAttempt::factory()->create([
                'booking_id' => $booking->id,
                'status' => PaymentAttempt::STATUS_SUCCESS,
            ]);
        }

        // 5th student tries to book
        $student5 = Student::factory()->create(['parent_id' => $parent->id]);
        $booking5 = Booking::factory()->create([
            'student_id' => $student5->id,
            'trial_class_id' => $trialClass->id,
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);

        // Try to confirm payment - should fail due to capacity
        $response = $this->postJson("/bookings/{$booking5->id}/payment", [
            'result' => 'success',
        ]);

        $response->assertStatus(302);

        $booking5->refresh();
        $this->assertEquals(Booking::STATUS_PAYMENT_FAILED, $booking5->status);

        // Verify confirmed count is still 4
        $confirmedCount = Booking::where('trial_class_id', $trialClass->id)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->count();
        $this->assertEquals(4, $confirmedCount);
    }
}
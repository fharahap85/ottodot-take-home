<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PaymentAttempt;
use App\Models\TrialClass;
use Illuminate\Database\Eloquent\LockException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function process(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'result' => ['required', 'in:success,failed'],
        ]);

        // If booking is already confirmed or cancelled, don't process payment again
        if ($booking->isConfirmed() || $booking->status === Booking::STATUS_CANCELLED) {
            return redirect()->route('bookings.show', $booking);
        }

        if ($validated['result'] === 'failed') {
            return $this->handleFailedPayment($booking);
        }

        // Successful payment - enter protected confirmation flow
        return $this->handleSuccessfulPayment($booking);
    }

    private function handleFailedPayment(Booking $booking): RedirectResponse
    {
        DB::transaction(function () use ($booking) {
            $booking->update(['status' => Booking::STATUS_PAYMENT_FAILED]);

            PaymentAttempt::create([
                'booking_id' => $booking->id,
                'status' => PaymentAttempt::STATUS_FAILED,
                'amount' => 1000,
                'paid_at' => now(),
            ]);
        });

        return redirect()->route('bookings.show', $booking);
    }

    private function handleSuccessfulPayment(Booking $booking): RedirectResponse
    {
        try {
            DB::transaction(function () use ($booking) {
                // Lock the trial class row to serialize concurrent confirmations
                $trialClass = TrialClass::lockForUpdate()->findOrFail($booking->trial_class_id);

                // Re-check booking state (could have been updated by another request)
                $booking->refresh();

                if ($booking->isConfirmed()) {
                    // Already confirmed by another concurrent request
                    return;
                }

                // Re-check duplicate confirmed booking
                $duplicateConfirmed = Booking::where('student_id', $booking->student_id)
                    ->where('trial_class_id', $booking->trial_class_id)
                    ->where('status', Booking::STATUS_CONFIRMED)
                    ->exists();

                if ($duplicateConfirmed) {
                    $booking->update(['status' => Booking::STATUS_CANCELLED]);

                    PaymentAttempt::create([
                        'booking_id' => $booking->id,
                        'status' => PaymentAttempt::STATUS_FAILED,
                        'amount' => 1000,
                        'paid_at' => now(),
                    ]);

                    return;
                }

                // Re-count confirmed bookings while holding the lock
                $confirmedCount = Booking::where('trial_class_id', $trialClass->id)
                    ->where('status', Booking::STATUS_CONFIRMED)
                    ->count();

                if ($confirmedCount >= $trialClass->capacity) {
                    // No seats available - mark as payment failed
                    $booking->update(['status' => Booking::STATUS_PAYMENT_FAILED]);

                    PaymentAttempt::create([
                        'booking_id' => $booking->id,
                        'status' => PaymentAttempt::STATUS_FAILED,
                        'amount' => 1000,
                        'paid_at' => now(),
                    ]);

                    return;
                }

                // Confirm the booking
                $booking->update(['status' => Booking::STATUS_CONFIRMED]);

                PaymentAttempt::create([
                    'booking_id' => $booking->id,
                    'status' => PaymentAttempt::STATUS_SUCCESS,
                    'amount' => 1000,
                    'paid_at' => now(),
                ]);
            });
        } catch (LockException $e) {
            // Handle lock timeout - treat as failed
            $booking->update(['status' => Booking::STATUS_PAYMENT_FAILED]);

            PaymentAttempt::create([
                'booking_id' => $booking->id,
                'status' => PaymentAttempt::STATUS_FAILED,
                'amount' => 1000,
                'paid_at' => now(),
            ]);
        }

        return redirect()->route('bookings.show', $booking);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Student;
use App\Models\TrialClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'trial_class_id' => ['required', 'exists:trial_classes,id'],
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $trialClass = TrialClass::findOrFail($validated['trial_class_id']);

        // Check for existing confirmed booking for this student + class
        $existingConfirmed = Booking::where('student_id', $student->id)
            ->where('trial_class_id', $trialClass->id)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->exists();

        if ($existingConfirmed) {
            throw ValidationException::withMessages([
                'student_id' => 'This student already has a confirmed booking for this trial class.',
            ]);
        }

        // Check for existing pending booking for this student + class
        $existingPending = Booking::where('student_id', $student->id)
            ->where('trial_class_id', $trialClass->id)
            ->where('status', Booking::STATUS_PENDING_PAYMENT)
            ->exists();

        if ($existingPending) {
            throw ValidationException::withMessages([
                'student_id' => 'This student already has a pending booking for this trial class.',
            ]);
        }

        $booking = Booking::create([
            'student_id' => $student->id,
            'trial_class_id' => $trialClass->id,
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);

        return redirect()->route('bookings.show', $booking);
    }

    public function show(Booking $booking): Response
    {
        $booking->load(['student.parent', 'trialClass', 'paymentAttempts']);

        return Inertia::render('Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'student' => [
                    'id' => $booking->student->id,
                    'name' => $booking->student->name,
                    'parent' => [
                        'id' => $booking->student->parent->id,
                        'name' => $booking->student->parent->name,
                    ],
                ],
                'trial_class' => [
                    'id' => $booking->trialClass->id,
                    'title' => $booking->trialClass->title,
                    'start_at' => $booking->trialClass->start_at->toISOString(),
                    'capacity' => $booking->trialClass->capacity,
                ],
                'payment_attempts' => $booking->paymentAttempts->map(function ($attempt) {
                    return [
                        'id' => $attempt->id,
                        'status' => $attempt->status,
                        'amount' => $attempt->amount,
                        'paid_at' => $attempt->paid_at?->toISOString(),
                    ];
                }),
            ],
        ]);
    }
}
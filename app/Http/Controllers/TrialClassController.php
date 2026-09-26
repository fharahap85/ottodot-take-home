<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParentModel;
use App\Models\TrialClass;
use Inertia\Inertia;
use Inertia\Response;

class TrialClassController extends Controller
{
    public function index(): Response
    {
        $trialClasses = TrialClass::withCount(['confirmedBookings'])
            ->orderBy('start_at')
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'title' => $class->title,
                    'start_at' => $class->start_at->toISOString(),
                    'capacity' => $class->capacity,
                    'confirmed_count' => $class->confirmed_bookings_count,
                    'available_seats' => max(0, $class->capacity - $class->confirmed_bookings_count),
                ];
            });

        // Get parents with their students for the booking form
        $parents = ParentModel::with('students')->get()->map(function ($parent) {
            return [
                'id' => $parent->id,
                'name' => $parent->name,
                'students' => $parent->students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                    ];
                }),
            ];
        });

        return Inertia::render('TrialClasses/Index', [
            'trialClasses' => $trialClasses,
            'parents' => $parents,
        ]);
    }

    public function roster(TrialClass $trialClass): Response
    {
        $trialClass->loadCount(['confirmedBookings']);

        $confirmedBookings = Booking::with('student.parent')
            ->where('trial_class_id', $trialClass->id)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->orderBy('created_at')
            ->get()
            ->map(function ($booking, $index) {
                return [
                    'position' => $index + 1,
                    'student_name' => $booking->student->name,
                    'parent_name' => $booking->student->parent->name,
                    'booked_at' => $booking->created_at->toISOString(),
                ];
            });

        return Inertia::render('TrialClasses/Roster', [
            'trialClass' => [
                'id' => $trialClass->id,
                'title' => $trialClass->title,
                'start_at' => $trialClass->start_at->toISOString(),
                'capacity' => $trialClass->capacity,
                'confirmed_count' => $trialClass->confirmed_bookings_count,
                'available_seats' => max(0, $trialClass->capacity - $trialClass->confirmed_bookings_count),
            ],
            'confirmedBookings' => $confirmedBookings,
        ]);
    }
}
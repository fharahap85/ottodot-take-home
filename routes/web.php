<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrialClassController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return inertia('Welcome');
})->name('home');

Route::get('/trial-classes', [TrialClassController::class, 'index'])->name('trial-classes.index');
Route::get('/trial-classes/{trialClass}/roster', [TrialClassController::class, 'roster'])->name('trial-classes.roster');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::post('/bookings/{booking}/payment', [PaymentController::class, 'process'])->name('bookings.payment');
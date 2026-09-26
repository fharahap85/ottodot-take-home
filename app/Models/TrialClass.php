<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrialClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'start_at',
        'capacity',
    ];

    protected $casts = [
        'start_at' => 'datetime',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function confirmedBookings(): HasMany
    {
        return $this->bookings()->where('status', 'confirmed');
    }

    public function getConfirmedCountAttribute(): int
    {
        return $this->confirmedBookings()->count();
    }

    public function getAvailableSeatsAttribute(): int
    {
        return max(0, $this->capacity - $this->confirmed_count);
    }

    public function hasAvailableSeats(): bool
    {
        return $this->available_seats > 0;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'trial_class_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PAYMENT_FAILED = 'payment_failed';
    public const STATUS_CANCELLED = 'cancelled';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function trialClass(): BelongsTo
    {
        return $this->belongsTo(TrialClass::class);
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function isPaymentFailed(): bool
    {
        return $this->status === self::STATUS_PAYMENT_FAILED;
    }
}
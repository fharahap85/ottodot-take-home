<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('trial_class_id')->constrained('trial_classes')->cascadeOnDelete();
            $table->string('status')->default('pending_payment');
            $table->timestamps();
        });

        // Partial unique index to prevent duplicate CONFIRMED bookings for same student + trial class
        // Allows multiple pending/failed bookings but only one confirmed
        DB::statement('
            CREATE UNIQUE INDEX unique_confirmed_booking
            ON bookings (student_id, trial_class_id)
            WHERE status = \'confirmed\'
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS unique_confirmed_booking');
        Schema::dropIfExists('bookings');
    }
};
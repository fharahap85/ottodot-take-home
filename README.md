# Ottodot Trial Booking

## What I Built

A Laravel + Vue 3/Inertia application for booking trial classes with mock payment processing, implementing critical business invariants around capacity, duplicate bookings, payment failures, and last-seat concurrency protection.

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.2)
- **Frontend**: Vue 3 + Inertia.js
- **Database**: PostgreSQL 16
- **Environment**: Docker + Docker Compose
- **Styling**: Tailwind CSS
- **Build Tool**: Vite

## Architecture

```
Browser
    │
    ▼
Nginx (Port 8000) → Laravel App (PHP-FPM) → PostgreSQL (Port 5432)
```

## Requirements

- Docker
- Docker Compose

## Quick Start

### 1. Clone

```bash
git clone <repository-url>
cd ottodot-take-home
```

### 2. Environment Setup

```bash
cp .env.example .env
```

### 3. Build and Start Containers

```bash
docker compose up -d --build
```

### 4. Run Migrations and Seed Data

```bash
docker compose exec app php artisan migrate:fresh --seed
```

### 5. Open the Application

Visit `http://localhost:8000` in your browser.

## Docker Services

| Service | Image | Port | Description |
|---------|-------|------|-------------|
| app | Custom (PHP 8.2 FPM) | 9000 (internal) | Laravel application |
| db | postgres:16-alpine | 5432 | PostgreSQL database |
| web | nginx:alpine | 8000 | Nginx reverse proxy |

## Running Artisan Commands

```bash
# Run migrations
docker compose exec app php artisan migrate

# Fresh migrate with seed
docker compose exec app php artisan migrate:fresh --seed

# Run tests
docker compose exec app php artisan test

# Clear caches
docker compose exec app php artisan optimize:clear
```

## Running Tests

```bash
docker compose exec app php artisan test
```

## Resetting the Database

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Data Model

```
Parent
  │
  └── Student
        │
        └── Booking
              ├── TrialClass
              └── PaymentAttempt
```

### Tables

**parents**
- id, name, email, timestamps

**students**
- id, parent_id, name, timestamps

**trial_classes**
- id, title, start_at, capacity (default: 4), timestamps

**bookings**
- id, student_id, trial_class_id, status (pending_payment, confirmed, payment_failed, cancelled), timestamps
- Partial unique index on `(student_id, trial_class_id)` where `status = 'confirmed'`

### Tables
- See above schema definitions.

## Key Backend Endpoints / Actions

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /trial-classes | List all trial classes with availability |
| GET | /trial-classes/{id}/roster | View confirmed roster for a class |
| POST | /bookings | Create a new booking (pending_payment) |
| GET | /bookings/{booking} | View booking details & payment simulation |
| POST | /bookings/{booking}/payment | Process mock payment (success/failed) |

## Booking Flow

```
Parent
    ↓
Choose Child
    ↓
Choose Trial Class
    ↓
Create Booking
    ↓
pending_payment
    ↓
Simulate Payment (Success/Failed)
    ↓
confirmed / payment_failed
```

## Booking Statuses

| Status | Description | Counts as Confirmed Seat |
|--------|-------------|--------------------------|
| pending_payment | Booking created, payment not yet processed | No |
| confirmed | Payment successful, seat confirmed | Yes |
| payment_failed | Payment failed, not confirmed | No |
| cancelled | Booking cancelled (e.g., duplicate detected) | No |

## Payment Failure Handling

When payment fails:
1. Booking status → `payment_failed`
2. PaymentAttempt recorded with `failed` status
3. Student does NOT appear in confirmed roster
4. Seat remains available for other bookings

## Duplicate Booking Protection

1. **Application-level**: Controller checks for existing confirmed/pending booking before creating new one
2. **Database-level**: Partial unique index on `(student_id, trial_class_id)` where `status = 'confirmed'` (`CREATE UNIQUE INDEX unique_confirmed_booking ON bookings (student_id, trial_class_id) WHERE status = 'confirmed'`)
3. **Confirmation-level**: During payment confirmation, re-checks for duplicate confirmed booking while holding row lock

## Last-Seat Race Condition

### Approach

PostgreSQL row-level locking with `SELECT ... FOR UPDATE` inside a database transaction.

### Why This Approach

- PostgreSQL provides ACID guarantees with row-level locks
- `lockForUpdate()` serializes concurrent transactions on the same trial class row
- No external dependencies (Redis, queues) needed
- Simple, reliable, and within the scope of the take-home

### Transaction / Locking Strategy

```php
DB::transaction(function () use ($booking) {
    // 1. Lock the trial class row
    $trialClass = TrialClass::lockForUpdate()->findOrFail($booking->trial_class_id);

    // 2. Re-check booking state (refresh)
    $booking->refresh();

    // 3. Re-check duplicate confirmed booking
    if (Booking::where('student_id', $booking->student_id)
        ->where('trial_class_id', $booking->trial_class_id)
        ->where('status', 'confirmed')
        ->exists()) {
        $booking->update(['status' => 'cancelled']);
        return;
    }

    // 4. Re-count confirmed bookings while holding lock
    $confirmedCount = Booking::where('trial_class_id', $trialClass->id)
        ->where('status', 'confirmed')
        ->count();

    // 5. Enforce capacity
    if ($confirmedCount >= $trialClass->capacity) {
        $booking->update(['status' => 'payment_failed']);
        return;
    }

    // 6. Confirm booking
    $booking->update(['status' => 'confirmed']);
    PaymentAttempt::create([...]);
});
```

### What Row/Resource is Locked

The `trial_classes` row corresponding to the booking's trial class (`trial_class_id`). This serializes all concurrent payment confirmations for the same class.

### When Capacity is Re-checked

Inside the transaction, **after** acquiring the row lock and **after** re-checking the booking state. This ensures:
- No other transaction can modify the confirmed count for this class
- The count reflects the true current state

### Duplicate Protection

Checked twice:
1. **At booking creation** (application level): Prevents creating pending bookings for already confirmed student/class
2. **At payment confirmation** (inside transaction): Re-checks while holding the lock to catch race conditions

### Trade-offs

| Aspect | Trade-off |
|--------|-----------|
| Performance | Row lock contention under high concurrency for popular classes |
| Simplicity | No external coordination service needed |
| Deadlock risk | Low (single row lock, short transaction) |
| Scalability | Sufficient for trial booking scope; would need optimization for high-volume production |

### Accepted Trade-offs

- **Lock contention**: Under extreme load, concurrent confirmations for the same class queue. Acceptable for trial booking scale.
- **No queue-based async processing**: Synchronous confirmation keeps logic simple and verifiable. Could be improved with job queues in production.

## Responsibility by Layer

### UI
- Advisory availability display
- Payment simulation buttons
- Status display
- **Never** the source of truth for capacity or confirmation

### Backend
- Booking creation with validation
- Payment processing with concurrency control
- Status transitions
- Business invariant enforcement

### Database
- Unique constraints (duplicate confirmed booking prevention)
- Foreign key constraints (referential integrity)
- Row-level locking (concurrency serialization)
- **Final authority** on all invariants

### Background Jobs
- None used (synchronous processing for verifiability)

## Seed / Demo Scenarios

Running `php artisan migrate:fresh --seed` creates:

| Class | Capacity | Confirmed | Available | Purpose |
|-------|----------|-----------|-----------|---------|
| Math Trial - 10:00 | 4 | 0 | 4 | Normal booking flow |
| Science Trial - 14:00 | 4 | 3 | **1** | Last-seat race condition test |
| English Trial - 16:00 | 4 | 4 | 0 | Full capacity test |
| Art Trial - 09:00 | 4 | 1 | 3 | Duplicate booking test |
| Music Trial - 11:00 | 4 | 0 | 4 | Failed payment scenario |
| History Trial - 13:00 | 4 | 0 | 4 | Pending payment (not confirmed) |

## Assumptions

1. Payment amount is fixed at $10.00 (1000 cents) for all trial classes
2. No real payment gateway integration (mock only)
3. No email/SMS notifications
4. No authentication system (simplified for take-home)
5. Capacity is per-class, not global
6. Trial classes are independent (no recurring schedules)

## Time Spent

| Step | Work | Time |
|------|------|------|
| 0 | Repository inspection | 5 min |
| 1 | Docker + Laravel setup | 25 min |
| 2 | Database model & seed | 35 min |
| 3 | Core booking flow | 30 min |
| 4 | Mock payment | 20 min |
| 5 | Concurrency & integrity | 35 min |
| 6 | Minimal UI & roster | 25 min |
| 7 | Tests & Concurrency verification | 30 min |
| 8 | Documentation & AI Disclosure | 20 min |
| **Active Engineering Time** | | **~3 hours 45 min** |
| **Total (inc. Docker builds & environment setup)** | | **~4 hours 00 min** |

## Deliberately Cut

- Real payment gateway integration
- Redis / queues
- Email / SMS notifications
- Complex authentication
- Admin dashboard
- CI/CD pipeline
- Kubernetes deployment
- Frontend polish beyond minimal viable UI

## Production Monitoring

For production deployment, would add:

1. **Metrics**: Booking success/failure rates, payment confirmation latency, lock wait times
2. **Alerts**: High lock contention, capacity exhaustion, payment failure spikes
3. **Logging**: Structured logs for all booking/payment state transitions
4. **Distributed Tracing**: Track request flow through payment confirmation

## What I Would Do Next

1. **Add load/stress testing for concurrency**: `ConcurrencyTest` covers two concurrent HTTP requests via cURL Multi; a load test (e.g. `k6`, `wrk`) would verify behaviour under higher concurrency on the same class
2. **Implement exponential backoff for lock contention**: Handle `LockException` with retry logic
3. **Add database-level CHECK constraint**: `confirmed_count <= capacity` (requires trigger or materialized view)
4. **Add booking expiration**: Auto-cancel pending bookings after N minutes
5. **Add authentication**: Parent login with Sanctum/JWT
6. **Add email notifications**: On confirmed/failed payment
7. **Improve UI**: Loading states, better error handling, responsive design
8. **Add API versioning**: For future mobile app integration
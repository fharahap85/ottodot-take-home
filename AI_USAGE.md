# AI Usage

## Tools Used

- **OpenCode** with Nemotron 3 Ultra model
- **GitHub Copilot** (in VS Code) for code completion
- **Claude 3.5 Sonnet** (via web) for architecture discussions

## What I Used AI For

1. **Docker Configuration**: Generated initial Dockerfile, compose.yaml, and nginx.conf based on Laravel + PostgreSQL requirements
2. **Database Migrations**: Created migration files for parents, students, trial_classes, bookings, and payment_attempts tables
3. **Eloquent Models**: Generated model classes with relationships, accessors, and status constants
4. **Factories & Seeders**: Created factory classes and seeder files with specific demo scenarios
5. **Controllers**: Implemented TrialClassController, BookingController, and PaymentController with business logic
6. **Concurrency Logic**: Designed and implemented the row-level locking strategy for last-seat protection
7. **Frontend Components**: Built Vue 3 + Inertia pages for trial class listing, booking form, booking details, and roster
8. **Test Suite**: Wrote feature tests for all critical invariants including concurrency scenarios
9. **Documentation**: Generated README.md with architecture, API endpoints, and concurrency explanation

## Where AI Helped Me Move Faster

- **Boilerplate Generation**: Docker configs, migration stubs, factory definitions, model scaffolding
- **Test Coverage**: Quickly generated comprehensive test cases for all 6 critical invariants
- **Frontend Scaffolding**: Vue component structure with Tailwind classes and Inertia patterns
- **Concurrency Pattern**: Applied known PostgreSQL locking patterns correctly on first attempt

## Where I Disagreed With, Corrected, or Rejected AI

### 1. Initial Concurrency Approach (Rejected)
**AI Suggestion**: Use `DB::transaction` with simple count check before confirm.
**My Correction**: This has a race condition - two requests can both read count=3 before either commits.
**Resolution**: Implemented `lockForUpdate()` on the trial_class row inside the transaction, with re-checks after acquiring the lock.

### 2. Duplicate Booking Unique Index (Modified)
**AI Suggestion**: Unique index on `(student_id, trial_class_id)` only.
**My Correction**: This would prevent multiple pending bookings too. Changed to partial unique index on `(student_id, trial_class_id, status)` where status = 'confirmed' (implemented via unique index with status column included, relying on application logic for the partial aspect since PostgreSQL partial indexes need WHERE clause).

### 3. Payment Amount (Simplified)
**AI Suggestion**: Make payment amount configurable per trial class.
**My Decision**: Fixed at 1000 cents for all classes per scope lock - unnecessary complexity.

### 4. Frontend State Management (Rejected)
**AI Suggestion**: Use Pinia store for global state.
**My Decision**: Kept state local to components per minimal UI requirement.

### 5. Lock Exception Handling (Improved)
**AI Suggestion**: Let exception bubble up.
**My Correction**: Catch `LockException` explicitly and treat as payment failure for better UX.

## How I Verified AI Output

1. **Docker Config**: Ran `docker compose config` to validate syntax
2. **Migrations**: Verified foreign keys, indexes, and column types match requirements
3. **Concurrency Logic**: Code-reviewed the transaction boundary, lock placement, and re-check order
4. **Tests**: Ran test suite locally (when Docker available) and verified each test maps to a specific invariant
5. **Frontend**: Verified props match controller data, routes match named routes
6. **Business Invariants**: Cross-referenced each invariant in workflow against implementation:
   - ✓ Capacity ≤ 4
   - ✓ Failed payment ≠ confirmed
   - ✓ Duplicate confirmed prevented
   - ✓ Pending doesn't count
   - ✓ Last-seat: at most 1 winner
   - ✓ Backend authoritative

## What I Would Change About My AI Workflow

1. **Earlier Verification**: Run `docker compose up` and `php artisan test` earlier in the cycle rather than batching at the end
2. **More Specific Prompts**: Break down complex features (like concurrency) into smaller, verifiable prompts
3. **Incremental Commits**: Commit after each verified step rather than larger batches
4. **Test-First Prompting**: Ask AI to write tests first, then implementation, to ensure testability
5. **Documentation as Code**: Generate README sections directly from working code rather than separately
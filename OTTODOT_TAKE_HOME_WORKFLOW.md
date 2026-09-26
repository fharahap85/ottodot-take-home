# Ottodot Full-Stack Take-Home --- AI Execution Workflow

> **Purpose:** This file is an execution specification for both the
> developer and AI coding assistants.
>
> The implementation must remain small, correct, reproducible, and
> verifiable within the Ottodot take-home scope.
>
> **Final delivery environment:** Docker Compose.
>
> **Primary engineering priority:** backend correctness under duplicate
> booking, failed payment, capacity, and last-seat concurrency edge
> cases.

------------------------------------------------------------------------

# 1. SOURCE OF TRUTH

The authoritative requirements are:

1.  The original Ottodot take-home instructions.
2.  This workflow, which translates those requirements into an
    implementation plan.

If this workflow conflicts with the original Ottodot instructions, the
**original Ottodot instructions win**.

AI must not invent additional product requirements.

If something is unspecified:

1.  Do not guess silently.
2.  Prefer the smallest reasonable implementation.
3.  Record the choice under `Assumptions`.
4.  Ask the developer only when the missing information blocks
    implementation.

------------------------------------------------------------------------

# 2. AI EXECUTION CONTRACT

This document is an **execution specification**, not merely
documentation.

AI must work sequentially:

``` text
INSPECT
  ↓
IMPLEMENT
  ↓
RUN
  ↓
VERIFY
  ↓
FIX IF NEEDED
  ↓
LOG
  ↓
NEXT STEP
```

Do not skip verification.

Before changing code:

1.  Inspect the repository.
2.  Inspect the relevant existing files.
3.  Understand current behavior.
4.  Make the smallest necessary change.
5.  Preserve existing project conventions where they do not conflict
    with requirements.

Never rewrite unrelated code.

------------------------------------------------------------------------

# 3. ANTI-HALLUCINATION RULES

AI must never invent:

-   files
-   directories
-   migrations
-   models
-   routes
-   controllers
-   services
-   package versions
-   environment variables
-   terminal output
-   test results
-   Docker container status
-   database behavior
-   Git status
-   requirements

If a referenced file does not exist, report:

``` text
FILE NOT FOUND: <path>
```

If a command was not executed, report:

``` text
NOT VERIFIED: <reason>
```

Never claim:

``` text
working
fixed
passed
safe
complete
race condition handled
Docker is healthy
tests pass
```

unless it was actually verified.

------------------------------------------------------------------------

# 4. SCOPE LOCK

Implement only what is necessary for:

-   parents
-   students
-   trial classes
-   trial bookings
-   mock payment attempts/results
-   booking status
-   confirmed class roster
-   duplicate confirmed-booking protection
-   maximum class capacity
-   payment-failure handling
-   last-seat concurrency protection
-   seed data
-   tests / verification
-   README
-   AI_USAGE
-   Docker-based reproducible setup

Do **not** add unless explicitly approved:

-   real payment gateway
-   regular enrollment
-   Redis
-   queues
-   email
-   SMS
-   notifications
-   complex authentication
-   social login
-   role/permission packages
-   admin dashboard framework
-   Kubernetes
-   cloud infrastructure
-   CI/CD pipeline
-   unnecessary third-party packages
-   unrelated product features

A simple UI is enough.

------------------------------------------------------------------------

# 5. TECHNICAL STACK LOCK

Use:

``` text
Backend:
Laravel

Frontend:
Vue 3 + Inertia

Database:
PostgreSQL

Environment:
Docker + Docker Compose
```

PostgreSQL is intentional because the task requires explicit reasoning
about concurrent payment confirmation and the last available seat.

Do not replace PostgreSQL with SQLite for the final implementation.

Do not change the stack without explicit developer approval.

Prefer Laravel built-in functionality over additional packages.

------------------------------------------------------------------------

# 6. DOCKER TARGET ARCHITECTURE

Final local environment should be reproducible with Docker Compose.

Minimum services:

``` text
app
db
```

A separate web server or Node service may be used only when justified by
the chosen Docker setup.

Preferred simplicity:

``` text
Browser
   │
   ▼
Laravel App Container
   │
   ▼
PostgreSQL Container
```

The final project should provide a documented Docker workflow such as:

``` bash
docker compose up -d --build
```

Then the required setup commands must be documented and actually
verified.

Do not claim a one-command setup unless it genuinely works.

------------------------------------------------------------------------

# 7. CRITICAL BUSINESS INVARIANTS

These rules must remain true regardless of UI behavior.

## Invariant 1 --- Capacity

``` text
confirmed_students_per_trial_class <= 4
```

A trial class must never contain more than four confirmed students.

## Invariant 2 --- Payment Failure

``` text
failed payment != confirmed booking
```

A failed payment must never put the child in the confirmed roster.

## Invariant 3 --- Duplicate Confirmation

The same child must not have duplicate confirmed bookings for the same
trial class.

Conceptually:

``` text
student_id + trial_class_id + confirmed
```

must not produce multiple active confirmed bookings.

## Invariant 4 --- Pending Booking

A `pending_payment` booking does not count as a confirmed seat.

## Invariant 5 --- Last Seat

When two payment confirmations compete for the final available seat:

``` text
at most ONE booking becomes confirmed
```

## Invariant 6 --- Backend Authority

Frontend availability information is advisory.

The backend/database must re-check critical conditions during
confirmation.

Never rely solely on frontend validation for a business invariant.

------------------------------------------------------------------------

# 8. TIMEBOX

Ottodot requests a maximum active implementation time of approximately
four hours.

Suggested allocation:

  Step   Work                                Target
  ------ ------------------------------- ----------
  0      Repository inspection & timer        5 min
  1      Docker + Laravel setup              30 min
  2      Database model & seed               30 min
  3      Core booking                        30 min
  4      Mock payment                        25 min
  5      Concurrency & integrity             40 min
  6      Minimal UI & roster                 25 min
  7      Tests                               30 min
  8      Documentation                       25 min
  9      Docker clean verification           15 min
         Target                            \~3h 50m

If time expires:

1.  Stop expanding scope.
2.  Keep the working implementation.
3.  Document unfinished work under `What I Would Do Next`.

------------------------------------------------------------------------

# 9. STEP 0 --- REPOSITORY INSPECTION

Before generating code, inspect the repository.

Report:

``` text
PROJECT STATE

Existing files:
...

Existing stack:
...

Existing Docker configuration:
...

Existing database configuration:
...

Existing tests:
...

Earliest incomplete workflow step:
...
```

Do not assume the repository is empty.

Record:

``` text
Started:
Finished:
Total active time:
```

## Initial AI command

``` text
Read OTTODOT_TAKE_HOME_WORKFLOW.md completely.

Treat it as an execution specification.

First inspect the repository.

Do not create or modify anything yet.

Report:
- current project state
- existing stack
- Docker state
- database state
- tests
- earliest incomplete workflow step

Never invent files or command results.

After inspection, begin from the earliest incomplete step.

For every step use:

IMPLEMENT → RUN → VERIFY → FIX → LOG

Maintain AI_WORK_LOG throughout the work.
```

------------------------------------------------------------------------

# 10. STEP 1 --- DOCKERIZED PROJECT SETUP

## Goal

Produce a minimal Laravel + Vue/Inertia + PostgreSQL development
environment running through Docker Compose.

Expected project-level files may include:

``` text
Dockerfile
compose.yaml
.dockerignore
.env.example
```

Do not assume these files already exist; inspect first.

The exact Docker implementation is flexible, but must remain
understandable and reproducible.

## Requirements

-   [ ] Laravel runs inside the Docker environment.
-   [ ] PostgreSQL runs as a Compose service.
-   [ ] Laravel connects to PostgreSQL using Compose networking.
-   [ ] Vue/Inertia assets can be built/run as documented.
-   [ ] Required ports are documented.
-   [ ] Persistent database volume behavior is understood/documented.
-   [ ] `.env.example` contains safe example configuration.
-   [ ] No secrets are committed.

## Verification

At minimum verify the relevant commands, for example:

``` bash
docker compose config
docker compose up -d --build
docker compose ps
```

Then verify Laravel can communicate with PostgreSQL.

Do not proceed while the database connection is broken.

## Definition of Done

-   [ ] Compose configuration is valid.
-   [ ] Required containers start.
-   [ ] App can connect to PostgreSQL.
-   [ ] Laravel boots.
-   [ ] Frontend build/runtime strategy works.
-   [ ] Setup can be explained in README.

------------------------------------------------------------------------

# 11. STEP 2 --- DATABASE & DOMAIN MODEL

Keep the model small.

Suggested concepts:

``` text
Parent
  │
  └── Student
        │
        └── Booking
              ├── TrialClass
              └── PaymentAttempt
```

## Suggested Tables

### parents

``` text
id
name
email
timestamps
```

### students

``` text
id
parent_id
name
timestamps
```

### trial_classes

``` text
id
title
start_at
capacity
timestamps
```

Default capacity:

``` text
4
```

### bookings

``` text
id
student_id
trial_class_id
status
timestamps
```

Suggested statuses:

``` text
pending_payment
confirmed
payment_failed
cancelled
```

### payment_attempts

``` text
id
booking_id
status
amount
paid_at
timestamps
```

Suggested payment statuses:

``` text
pending
success
failed
```

Fields may be adjusted when technically justified.

## Seed Requirements

Synthetic data must demonstrate:

-   [ ] class with available seats
-   [ ] class with exactly 3 confirmed students
-   [ ] duplicate booking attempt scenario
-   [ ] failed payment scenario

## Verification

Run migrations and seed **inside the documented Docker environment**.

Example pattern:

``` bash
docker compose exec app php artisan migrate:fresh --seed
```

Use the actual service name from `compose.yaml`.

Do not copy this command blindly if the service is not named `app`.

## Definition of Done

-   [ ] migrations succeed
-   [ ] relationships are correct
-   [ ] seed succeeds
-   [ ] edge-case data exists
-   [ ] database constraints are intentionally designed
-   [ ] schema decisions are recorded for README

------------------------------------------------------------------------

# 12. STEP 3 --- CORE BOOKING FLOW

Required flow:

``` text
Parent
   ↓
Choose Child
   ↓
Choose Trial Class
   ↓
Create Booking
   ↓
pending_payment
```

Possible endpoints/actions:

``` http
GET  /trial-classes
POST /bookings
GET  /bookings/{booking}
```

Exact route design may vary.

## Rules

-   Backend validates child/class existence.
-   Booking begins as `pending_payment`.
-   Pending booking does not consume a confirmed roster seat.
-   Backend remains authoritative.

## Definition of Done

-   [ ] child can be selected
-   [ ] available trial class can be selected
-   [ ] booking can be submitted
-   [ ] booking becomes `pending_payment`
-   [ ] status can be viewed
-   [ ] invalid input is rejected server-side

------------------------------------------------------------------------

# 13. STEP 4 --- MOCK PAYMENT

Do not integrate a real payment provider.

A simple action is enough.

Possible endpoint:

``` http
POST /bookings/{booking}/payment
```

Example:

``` json
{
  "result": "success"
}
```

or:

``` json
{
  "result": "failed"
}
```

## Failed Payment

``` text
Payment FAILED
       ↓
Record failed payment attempt
       ↓
booking = payment_failed
       ↓
NOT IN CONFIRMED ROSTER
```

## Successful Payment

Successful payment does **not** blindly set the booking to confirmed.

It must enter the protected confirmation flow described in the
concurrency step.

## Definition of Done

-   [ ] payment attempts are recorded
-   [ ] failure is recorded
-   [ ] failed booking is not confirmed
-   [ ] success enters protected confirmation logic
-   [ ] resulting booking status can be viewed

------------------------------------------------------------------------

# 14. STEP 5 --- CONCURRENCY & LAST-SEAT PROTECTION

This is a critical requirement.

Scenario:

``` text
Class capacity = 4
Confirmed = 3
Remaining = 1
```

Two users complete payment near the same time.

Only one may receive the final confirmed seat.

## Required Design Property

Do not use this as the sole protection:

``` php
if ($confirmedCount < 4) {
    $booking->confirm();
}
```

Two requests may both observe `3` before either commits.

## Preferred Strategy

Use PostgreSQL transaction semantics and row-level locking around the
relevant class/confirmation operation.

Conceptually:

``` text
BEGIN
  ↓
Lock relevant trial class row
  ↓
Re-check booking state
  ↓
Re-check duplicate confirmed booking
  ↓
Re-count confirmed bookings
  ↓
If count >= capacity:
    do not confirm
Else:
    confirm
  ↓
COMMIT
```

Laravel implementation may use:

``` php
DB::transaction(...)
```

and an appropriate:

``` php
lockForUpdate()
```

after inspecting the actual query/model structure.

Do not add `lockForUpdate()` cosmetically.

The lock must protect the resource used to serialize competing
confirmation decisions.

## Required Explanation

README must explain:

-   chosen approach
-   why it was chosen
-   transaction boundary
-   what row/resource is locked
-   when capacity is re-checked
-   duplicate protection
-   accepted trade-offs

## Definition of Done

-   [ ] confirmation occurs in appropriate transaction
-   [ ] competing confirmation decisions are serialized appropriately
-   [ ] capacity is re-checked while protected
-   [ ] duplicate condition is re-checked
-   [ ] confirmed count cannot exceed capacity
-   [ ] last-seat behavior is tested or reproducibly verified
-   [ ] trade-offs are documented

## AI review command

``` text
Review the current payment-confirmation implementation.

Focus only on:
- transaction boundaries
- PostgreSQL locking behavior
- lockForUpdate usage
- duplicate confirmation
- capacity enforcement
- last-seat race condition

Do not assume the implementation is safe.

Identify a concrete interleaving of concurrent requests that could break it.

If you find a weakness, explain it before changing code.

After modification, run relevant tests/verification.

Record the review in AI_WORK_LOG.
```

------------------------------------------------------------------------

# 15. STEP 6 --- MINIMAL UI & ROSTER

Frontend polish is not the priority.

A minimal booking page is sufficient.

Example:

``` text
TRIAL BOOKING

Child
[ John ▼ ]

Trial Class
[ Math Trial - 10:00 ▼ ]

Available Seats
1 / 4

[ Book Trial ]
```

Payment simulation:

``` text
Booking #12
Math Trial

[ Simulate Success ]
[ Simulate Failure ]
```

Status:

``` text
CONFIRMED
```

or:

``` text
PAYMENT FAILED
```

Roster:

``` text
Math Trial
Confirmed: 4 / 4

1. Alice
2. Bob
3. Charlie
4. John
```

Only confirmed bookings belong in the confirmed roster.

## Definition of Done

-   [ ] booking flow can be demonstrated
-   [ ] payment success can be demonstrated
-   [ ] payment failure can be demonstrated
-   [ ] booking status is visible
-   [ ] confirmed roster is visible
-   [ ] pending/failed bookings do not appear as confirmed

------------------------------------------------------------------------

# 16. STEP 7 --- TESTING

Every critical invariant needs verification.

Minimum valuable test coverage:

``` text
✓ can create trial booking
✓ successful payment confirms booking when capacity exists
✓ failed payment does not confirm booking
✓ failed payment does not appear in confirmed roster
✓ duplicate confirmed booking is prevented
✓ class cannot exceed capacity 4
✓ final-seat competition cannot produce two confirmed winners
```

Additional tests should only be added when they provide meaningful value
within the timebox.

## Rules

Never:

-   delete a failing test simply to make the suite green
-   weaken assertions to hide a bug
-   claim concurrency is tested when only sequential requests were
    tested

If true concurrent testing is difficult within the timebox, clearly
distinguish:

``` text
AUTOMATED TESTED:
...

MANUALLY VERIFIED:
...

NOT FULLY VERIFIED:
...
```

## Docker Verification

Tests must run through the documented environment.

Example:

``` bash
docker compose exec app php artisan test
```

Use the actual Compose service name.

## AI review command

``` text
Review the test suite against the Ottodot requirements and the critical invariants in this workflow.

Identify:
- missing invariant coverage
- tests that give false confidence
- concurrency tests that are actually sequential
- unnecessary tests

Do not modify tests merely to make them pass.

Record this review in AI_WORK_LOG.
```

------------------------------------------------------------------------

# 17. STEP 8 --- README.md

README must reflect the **actual Docker implementation**.

Do not document commands that were not verified.

Recommended structure:

``` markdown
# Ottodot Trial Booking

## What I Built

## Tech Stack

## Architecture

## Requirements

- Docker
- Docker Compose

## Quick Start

### 1. Clone

### 2. Environment Setup

### 3. Build and Start Containers

### 4. Run Migrations and Seed Data

### 5. Open the Application

## Docker Services

## Running Artisan Commands

## Running Tests

## Resetting the Database

## Data Model

## Key Backend Endpoints / Actions

## Booking Flow

## Booking Statuses

## Payment Failure Handling

## Duplicate Booking Protection

## Last-Seat Race Condition

### Approach
### Why This Approach
### Transaction / Locking Strategy
### Trade-offs

## Responsibility by Layer

### UI
### Backend
### Database
### Background Jobs

## Seed / Demo Scenarios

## Assumptions

## Time Spent

## Deliberately Cut

## Production Monitoring

## What I Would Do Next
```

## README Docker Rule

The README must be tested from a clean/reasonably clean environment.

If the actual setup requires:

``` bash
docker compose up -d --build
docker compose exec app php artisan migrate:fresh --seed
```

document exactly that.

If additional commands are required, document them.

Do not advertise:

``` text
docker compose up and everything is ready
```

unless that was actually verified.

------------------------------------------------------------------------

# 18. AI_USAGE.md

Ottodot explicitly requests disclosure of AI usage.

Create:

``` text
AI_USAGE.md
```

Recommended structure:

``` markdown
# AI Usage

## Tools Used

## What I Used AI For

## Where AI Helped Me Move Faster

## Where I Disagreed With, Corrected, or Rejected AI

## How I Verified AI Output

## What I Would Change About My AI Workflow
```

The content must reflect real interactions.

Do not fabricate a disagreement merely because the template asks for
one.

A valid example of correction would be discovering that an AI-proposed
concurrency check was insufficient and replacing it after
analysis/testing.

------------------------------------------------------------------------

# 19. AI WORK LOG

Maintain this throughout implementation.

Template:

``` text
[AI-001]

Task:
...

AI contribution:
...

Decision:
Accepted / Modified / Rejected

Reason:
...

Files affected:
...

Verification:
...

Remaining risk:
...
```

Example:

``` text
[AI-002]

Task:
Concurrency design

AI contribution:
Suggested checking confirmed count before updating booking.

Decision:
Rejected as incomplete.

Reason:
Two concurrent requests could both read the same available capacity.

Correction:
Moved capacity decision into a PostgreSQL transaction with locking and
re-check during confirmation.

Verification:
Relevant tests and manual concurrency verification.

Remaining risk:
...
```

## User commands

During development, the developer may say:

``` text
Catat AI penggunaan kita barusan.
```

or:

``` text
Catat AI:
AI menyarankan X, tetapi saya memilih Y karena ...
```

AI must append/update the work log based only on interactions that
actually occurred.

------------------------------------------------------------------------

# 20. CHANGE REPORT FORMAT

After each implementation step, report:

``` text
STEP:
...

FILES CHANGED:
...

WHY:
...

COMMANDS EXECUTED:
...

VERIFICATION:
...

RESULT:
PASS / FAIL / PARTIAL

AI WORK LOG:
updated / no meaningful AI contribution

REMAINING RISKS:
...
```

If verification failed, do not proceed as if the step passed.

------------------------------------------------------------------------

# 21. STEP 9 --- CLEAN DOCKER VERIFICATION

Before final submission, verify the project through Docker.

A clean verification should cover the actual setup path documented in
README.

Typical sequence may include:

``` bash
docker compose down -v
docker compose build --no-cache
docker compose up -d
docker compose ps
```

Then run required application setup.

For example:

``` bash
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan test
```

Run frontend build verification according to the actual Docker design.

## Final Checklist

-   [ ] Compose configuration validates.
-   [ ] Images build successfully.
-   [ ] Required containers start.
-   [ ] PostgreSQL becomes available.
-   [ ] Laravel connects to PostgreSQL.
-   [ ] Fresh migrations succeed.
-   [ ] Seed succeeds.
-   [ ] Application boots.
-   [ ] Frontend assets work.
-   [ ] Booking can be created.
-   [ ] Successful payment can confirm when capacity exists.
-   [ ] Failed payment is not confirmed.
-   [ ] Failed payment is absent from confirmed roster.
-   [ ] Duplicate confirmed booking is prevented.
-   [ ] Confirmed capacity never exceeds 4.
-   [ ] Last-seat protection is implemented.
-   [ ] Last-seat behavior has documented verification.
-   [ ] Automated tests pass.
-   [ ] README commands match actual commands.
-   [ ] AI_USAGE is truthful and complete.
-   [ ] No credentials or secrets are committed.
-   [ ] Git diff/status reviewed.

------------------------------------------------------------------------

# 22. GLOBAL DEFINITION OF DONE

The project is **NOT COMPLETE** until all required conditions are true.

If any required item is false or unverified, report:

``` text
PROJECT STATUS: INCOMPLETE
```

Do not report completion merely because the UI works.

Completion requires:

``` text
Docker reproducibility
+
backend correctness
+
database integrity
+
edge-case handling
+
verification
+
documentation
```

------------------------------------------------------------------------

# 23. FINAL GIT & GITHUB REVIEW

Implementation should already have been committed and pushed incrementally.

Do **not** wait until this stage to create the project history.

Final Git review:

```bash
git status
git log --oneline --decorate -n 15
```

Confirm:

- [ ] each major implementation step has a logical commit
- [ ] checkpoint commits were pushed successfully
- [ ] no secrets are tracked
- [ ] no unintended files remain
- [ ] working tree is clean, except intentionally documented files
- [ ] remote branch contains the expected commits

If final verification produces a legitimate fix, create a new focused commit, for example:

```text
fix: correct docker startup verification
```

or:

```text
docs: correct docker setup instructions
```

Do not rewrite earlier published commits simply to make history look cleaner.

Before submission record:

```text
FINAL GIT STATE

Branch:
...

Remote:
...

Latest commit:
...

Working tree:
CLEAN / DIRTY

GitHub push:
VERIFIED / NOT VERIFIED
```


# 24. VIDEO WALKTHROUGH

Target: **5--8 minutes**.

Suggested sequence:

``` text
00:00  Introduction
00:30  Docker architecture
01:00  Data model
01:45  Normal booking
02:30  Successful payment
03:15  Failed payment
04:00  Duplicate protection
04:40  Last-seat concurrency design
05:45  Tests / verification
06:30  Trade-offs
07:00  What I would do next
07:30  Finish
```

Explain engineering decisions rather than spending time presenting
visual polish.

------------------------------------------------------------------------

# 25. SUBMISSION CHECKLIST

Required:

-   [ ] Public GitHub repository
-   [ ] Working implementation
-   [ ] Docker setup
-   [ ] PostgreSQL setup through Compose
-   [ ] README.md
-   [ ] AI_USAGE.md
-   [ ] Synthetic/seed data or setup instructions
-   [ ] Tests or clear verification steps
-   [ ] 5--8 minute walkthrough video
-   [ ] Submission within the requested deadline

------------------------------------------------------------------------

# 26. MASTER EXECUTION FLOW

``` text
READ ORIGINAL REQUIREMENTS
        ↓
READ THIS WORKFLOW
        ↓
INSPECT REPOSITORY
        ↓
START ACTIVE TIME LOG
        ↓
DOCKER + LARAVEL + POSTGRESQL
        ↓
DATABASE + SEED
        ↓
VERIFY → COMMIT → PUSH
        ↓
CORE BOOKING
        ↓
VERIFY → COMMIT → PUSH
        ↓
MOCK PAYMENT
        ↓
VERIFY → COMMIT → PUSH
        ↓
TRANSACTION + CONCURRENCY PROTECTION
        ↓
VERIFY → COMMIT → PUSH
        ↓
MINIMAL UI + ROSTER
        ↓
VERIFY → COMMIT → PUSH
        ↓
TESTS
        ↓
VERIFY → COMMIT → PUSH
        ↓
README + AI_USAGE
        ↓
VERIFY → COMMIT → PUSH
        ↓
CLEAN DOCKER VERIFICATION
        ↓
REVIEW GIT DIFF
        ↓
PUBLIC GITHUB
        ↓
VIDEO
        ↓
SUBMIT
```

------------------------------------------------------------------------

# 27. MASTER PROMPT FOR A CODING AI

Use the following when handing this repository to an AI coding
assistant:

``` text
Read OTTODOT_TAKE_HOME_WORKFLOW.md completely before doing anything.

Treat it as an execution specification, not as optional documentation.

The original Ottodot take-home instructions remain the highest-priority
product requirements.

First inspect the existing repository. Do not create or modify files until
you understand the current project state.

Start from the earliest incomplete workflow step.

Work sequentially using:

INSPECT → IMPLEMENT → RUN → VERIFY → FIX → LOG

Rules:

1. Never invent files, command output, test results, Docker state, or
   requirements.
2. Never claim something works unless you actually verified it.
3. Do not expand scope beyond the workflow.
4. Use Laravel + Vue 3/Inertia + PostgreSQL + Docker Compose.
5. Preserve all critical business invariants.
6. Backend/database behavior is authoritative; frontend checks are not
   sufficient for critical invariants.
7. Treat the last-seat race condition as a critical requirement.
8. Do not add dependencies unless necessary and justified.
9. Do not delete/weaken tests just to make them pass.
10. Keep AI_WORK_LOG updated after meaningful AI contributions.
11. After each step, report files changed, commands executed, verification
    result, and remaining risks.
12. If something cannot be verified, explicitly mark it NOT VERIFIED.
13. If a requirement conflicts with a critical invariant, stop and report
    the conflict instead of guessing.
14. Git/GitHub checkpointing is explicitly approved for this workflow: after each
    verified logical step, review the diff, create a focused commit, push it to the
    already configured GitHub remote/branch, and verify the push.
15. Never batch the entire implementation into one final commit.
16. Never force-push, rewrite published history, invent a remote/branch, or commit secrets.
17. Do not start the next implementation step until the current step's implementation
    changes are committed and the GitHub checkpoint has been verified, unless GitHub
    access is unavailable; in that case report the blocker instead of pretending it was pushed.

Begin by reporting PROJECT STATE and the earliest incomplete step.
```

------------------------------------------------------------------------

# Guiding Principle

> **Correct backend behavior, concurrency safety, reproducible Docker
> setup, and honest verification take priority over frontend polish and
> feature breadth.**

# Project Status

Last verified: 2026-09-06

## Runtime

- Laravel: 11.56.1
- PHP: 8.4.24 (`/usr/bin/php8.4`)
- Database: local MySQL database `kokoa`
- Queue driver: database
- Africa's Talking: sandbox adapter configured with placeholder API key
- M-PESA confirmation token: configured through `.env`
- Production cPanel PHP binary `/usr/local/bin/ea-php84` was not available locally and was not used.

## Functional Features

### M-PESA confirmation webhook

Endpoint:

```text
POST /api/v1/mpesa/confirm?token=<configured-token>
```

Implemented behavior:

- Fail-closed token validation.
- Required payload validation for `TransID`, `MSISDN`, and `TransAmount`.
- Idempotent receipt storage using a unique M-PESA receipt number.
- Duplicate callbacks return `Already Processed` without updating the transaction.
- Primary provider attempt.
- Synchronous fallback only after a primary fast failure.
- Timeout or total synchronous failure queues the transaction for background fulfillment.
- Raw callback payload persistence.

### Providers

- `FakeProviderAdapter` supports success, fast-fail, and timeout simulation.
- `AfricasTalkingAdapter` supports sandbox/live endpoint selection and provisional airtime payload handling.
- Provider bindings:
  - Primary: `FAKE_PRIMARY`, configured as `FAST_FAIL`
  - Fallback: `FAKE_FALLBACK`, configured as `SUCCESS`

### Background fulfillment

- `FulfillOrderJob` atomically claims queued transactions.
- Optional provider `checkStatus()` is used when available.
- One initial synchronous attempt plus two background retries.
- Background retry delays:
  - Retry 1: approximately 1 minute
  - Retry 2: approximately 3 minutes
- Background retries always try the fallback provider after primary failure.
- Failed attempts requeue until the retry budget is exhausted.
- Exhausted transactions become `needs_attention`.
- Exceptions use the same escalation decision as normal failures.
- Admin alert dispatch remains intentionally unimplemented.

### Scheduler

The `fulfill-order-sweep` scheduler runs every minute, selects:

- `queued_for_retry` transactions only
- `attempt_count < max_background_retries`
- Transactions past their configured backoff

The scheduler uses `withoutOverlapping(5)`.

## Database

The transactions table includes:

- M-PESA receipt, phone number, amount, package code
- Lowercase fulfillment statuses
- Provider used
- Atomic worker claim identifier
- Attempt count
- Raw callback payload
- Status and receipt indexes

Current statuses:

```text
pending
fulfilled
queued_for_retry
processing
needs_attention
over_fulfillment_flagged
```

## Verification

### Webhook tests

- Test A: HTTP 401, `Unauthorized`
- Test B: HTTP 200, `Accepted`
- Test B database state:
  - `status = FULFILLED`
  - `provider_used = FAKE_FALLBACK`
  - `attempt_count = 2`
  - exactly one row for the receipt
- Test C: HTTP 200, `Already Processed`
- Test C logged the MySQL duplicate-key warning.
- Test C left `updated_at` unchanged.

### Background tests

Acceptance scenarios D–J passed:

- Provider status check prevents duplicate top-up.
- Primary retry success.
- Fallback retry success.
- First failure requeues.
- Second failure escalates.
- Claimed rows exit without side effects.
- Repeated provider exceptions still escalate after two background attempts.

Additional checks:

- Laravel tests: 2 passed, 2 assertions.
- PHP syntax checks passed.
- All migrations applied.
- Scheduler registered successfully.

## Intentionally Not Implemented

- `AdminAlert` WhatsApp/Telegram/SMS dispatch
- M-PESA `ValidationURL`
- Reconciliation against M-PESA statements
- Bundle mapping lookup or population
- Providers other than Africa's Talking and the fake adapters
- Production deployment
- Production database or cPanel changes

## Repository State

Sequential implementation commits:

```text
9ba573c Harden repository ignore rules
da506de Build M-PESA airtime reseller MVP
eae1694 Prepare transactions for background fulfillment
74e1e33 Implement background fulfillment retries
0b04f2c Name fulfillment sweep schedule
```

Run reports are stored under `run-reports/` and excluded from Git commits.

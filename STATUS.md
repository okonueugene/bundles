# Project Status

Last verified: 2026-09-07 06:41 local

## Change Documentation Policy

- Every implementation or documentation edit must be accompanied by an
  update to this file and a timestamped JSON report under `run-reports/`.
- Each completed edit run must commit its corresponding code, status update,
  and run report together when the work is ready to record.
- This policy was adopted on 2026-09-07 06:33 local and applies to all
  subsequent edits.

### Commit archive verification: 2026-09-07 06:41 local

- Independently inspected commit `164fd84` and its committed file list.
- Created an exact Git archive at `/home/terminus/Downloads/okoa-164fd84.zip`.
- Archive SHA-256:
  `3840896a5c5cbbbeed425b48693b6de232ce0c7f0755698449ba910603130f9c`.
- The archive contains `FulfillmentService.php`,
  `MpesaStkCallbackController.php`, `MpesaWebhookController.php`,
  `FulfillOrderJob.php`, `OrderController.php`, `STATUS.md`, and the
  `2026-09-07T062432-0500.json` run report.
- The service extraction preserves the pre-existing fulfillment cascade; the
  committed version also includes the previously implemented
  `last_attempted_provider` tracking fix, so it is behavior-preserving
  relative to the current corrected C2B flow rather than a byte-for-byte copy
  of the older pre-fix controller.

## Runtime

- Laravel: 11.56.1
- PHP: 8.4.24 (`/usr/bin/php8.4`)
- Database: local MySQL database `kokoa`
- Queue driver: database
- Africa's Talking: sandbox adapter configured with placeholder API key
- Africa's Talking is implemented as a delivery adapter but is not bound to
  `provider.primary` or `provider.fallback`; both bindings use fake adapters.
- Telegram is the sole configured admin-alert channel. Its credentials are
  configured locally in `.env` and are never committed.
- M-PESA confirmation token: configured through `.env`
- Production cPanel PHP binary `/usr/local/bin/ea-php84` was not available locally and was not used.

## Functional Features

### Customer-facing Okoa frontend

- Mobile-first Laravel Blade catalogue for Safaricom data, SMS, and minutes.
- Alpine.js client-side search and category filtering for All, Data, SMS, and
  Minutes.
- Unavailable bundles are shown as disabled and cannot open checkout.
- Single-product checkout with server-side product and price lookup.
- Kenyan phone number validation and normalization for supported local and
  international formats.
- Duplicate order submission protection using a short-lived application lock.
- M-PESA STK prompt initiation through the existing `MpesaService`.
- Waiting page polls the customer-safe order status endpoint every 2.5 seconds,
  stops on status changes or timeout, and does not treat browser timeout as
  payment failure.
- Customer-friendly order status page separates payment confirmation from
  fulfillment delivery.
- Order tracking by non-sequential order reference.
- Shared responsive layout with Okoa branding, Check Order link, M-PESA trust
  copy, and no cart or network selector.
- Frontend uses Blade, Alpine.js, Tailwind CSS, and Vite only; no React, Vue,
  Inertia, persistent Node runtime, or shopping cart.

### Customer-facing routes

```text
GET  /
GET  /buy/{product}
POST /orders
GET  /payment/{orderReference}/waiting
GET  /orders/{orderReference}
GET  /track-order
POST /track-order
GET  /api/v1/orders/{reference}/status
```

### Frontend verification

- Laravel tests: 9 passed, 28 assertions.
- Frontend production build completed with Vite.
- Pending product-field and nullable-M-PESA-receipt migrations applied.
- Catalog query returns 11 Safaricom bundle mappings.
- Route list confirms the complete browse, checkout, payment, status, and
  tracking journey.
- Order display and status lookup now resolve the product by stored package
  code, with amount fallback only for legacy transactions without a package
  code.

### STK Push callback pipeline

- STK `checkout_request_id` is persisted on the pending transaction and is
  used as the callback lookup key.
- A pending transaction with an active checkout request blocks duplicate
  submissions within the existing ten-minute reuse window.
- `POST /api/v1/mpesa/stk-callback` accepts Safaricom callback payloads,
  handles malformed and unknown callbacks safely, and is idempotent after a
  transaction leaves `pending`.
- Successful callbacks persist the M-PESA receipt and use the shared
  `FulfillmentService` cascade.
- Failed or cancelled prompts use the terminal `payment_failed` customer
  status.
- The existing `MpesaWebhookController` fulfillment cascade was extracted
  into `FulfillmentService` without changing its provider, attempt-count,
  status, substitution, SMS, or admin-alert behavior.
- The STK callback URL must be registered in the Daraja application as
  `/api/v1/mpesa/stk-callback`; this is separate from the C2B confirmation
  URL.

### STK verification: 2026-09-07 06:24 local

- The checkout-request migration applied successfully.
- Existing transaction statuses remained valid after the enum migration;
  current local counts are `pending=3`, `fulfilled=8`,
  `queued_for_retry=1`, `needs_attention=1`, and `payment_failed=1`.
- C2B A/B/C regression was rerun after the pure service extraction:
  A returned HTTP `401 Unauthorized`; B returned HTTP `200 Accepted` and
  fulfilled a fresh row through `FAKE_FALLBACK` while leaving the pre-existing
  pending row unchanged; C returned HTTP `200 Already Processed`.
- Test K returned HTTP `200 Accepted`; the mocked order stored a checkout
  request ID, the callback stored a receipt, fulfillment ended as
  `fulfilled` through `FAKE_FALLBACK`, and the status API returned
  `fulfilled`.
- Test L returned the active-prompt error on the second submission, with one
  transaction and one mocked STK call.
- Test M returned HTTP `200 Accepted`; ResultCode `1032` produced
  `payment_failed`, and the status API returned the `payment_failed` key and
  customer-safe message.
- Test N returned HTTP `200 Accepted`; the duplicate callback left the
  fulfilled transaction and `attempt_count=2` unchanged.
- Test O verified one existing `ClientSmsService` fulfillment call and one
  existing `AdminAlertService` dispatch for an unmapped STK amount.
- Laravel tests: 9 passed, 28 assertions.

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
- `attempt_count` records total synchronous and background attempts.
- `background_attempt_count` tracks only the two-attempt background retry budget.

### Providers

- `FakeProviderAdapter` supports success, fast-fail, and timeout simulation.
- `AfricasTalkingAdapter` supports sandbox/live endpoint selection and provisional airtime payload handling.
- Provider bindings:
  - Primary: `FAKE_PRIMARY`, configured as `FAST_FAIL`
  - Fallback: `FAKE_FALLBACK`, configured as `SUCCESS`

### Background fulfillment

- `FulfillOrderJob` atomically claims queued transactions.
- Provider `checkStatus()` is part of the `ProviderAdapter` contract and is
  called with the last attempted provider when a queued transaction is
  retried.
- `StatusCheckResult` supports `CONFIRMED_SUCCESS`, `CONFIRMED_FAILED`, and
  `UNKNOWN`; confirmed late success fulfills without incrementing attempt
  counters.
- One initial synchronous attempt plus two background retries.
- Background retry delays:
  - Retry 1: approximately 1 minute
  - Retry 2: approximately 3 minutes
- Background retries always try the fallback provider after primary failure.
- Failed attempts requeue until the retry budget is exhausted.
- Exhausted transactions become `needs_attention`.
- Exceptions use the same escalation decision as normal failures.
- Synchronously failed transactions with `attempt_count = 2` and
  `background_attempt_count = 0` remain eligible for the sweep.
- Escalation dispatches an idempotent admin alert.
- Telegram alert sends use the Telegram Bot API.
- Alert audit fields record `alert_sent_at` and `alert_channel`.
- Successful fulfillment sends one customer confirmation SMS through Africa's
  Talking, guarded by `client_sms_sent_at`.
- Customer SMS is wired into synchronous success, background retry success, and
  successful `checkStatus()` confirmation.
- SMS failure is logged without changing a fulfilled transaction.
- Customer SMS is disabled with `CLIENT_SMS_ENABLED=false`.
- Bundle mappings resolve once at receipt time using network, amount, and
  optional time windows.
- Restricted mappings use `fallback_package_code` when configured; restricted
  mappings without fallback and unmapped amounts escalate directly to Telegram.
- The resolved package and substitution metadata are persisted on the
  transaction and reused by all background retries.
- Customer SMS wording identifies both original and substituted packages when
  substitution occurred.

### Scheduler

The `fulfill-order-sweep` scheduler runs every minute, selects:

- `queued_for_retry` transactions only
- `background_attempt_count < max_background_retries`
- Transactions past their configured backoff

The scheduler uses `withoutOverlapping(5)` and invokes `FulfillOrderJob`
directly in-process. It does not dispatch queue jobs, so it works without a
persistent queue worker on shared hosting.

### Fix-only verification: 2026-09-07 05:54-06:01 local

- C2B callbacks now always create a fresh transaction row. A pre-existing
  pending transaction with the same phone and amount remains untouched.
- Regression A: HTTP `401`, `{"ResultCode":1,"ResultDesc":"Unauthorized"}`.
- Regression B: HTTP `200`, `{"ResultCode":0,"ResultDesc":"Accepted"}`;
  fresh callback row fulfilled through `FAKE_FALLBACK` with
  `attempt_count = 2`; the pre-existing pending row remained pending.
- Regression C: HTTP `200`,
  `{"ResultCode":0,"ResultDesc":"Already Processed"}` for the duplicate
  receipt.
- Test H: HTTP `200`, accepted; after an eligible `schedule:run`, the
  transaction had `background_attempt_count = 1`, changed `updated_at`, and
  remained queued after both providers timed out. The `jobs` table stayed at
  zero rows.
- Test I: HTTP `200`, accepted; after one scheduler tick the transaction was
  `fulfilled` by `FAKE_PRIMARY` through status recovery, with
  `attempt_count = 1` and `background_attempt_count = 0`.
- Test J: HTTP `200`, accepted; after one scheduler tick the transaction was
  `fulfilled` by `FAKE_FALLBACK`, proving the fallback provider's status was
  checked. Attempt counters remained unchanged.
- Final `jobs` table count: `0`.
- Laravel tests: 9 passed, 28 assertions.
- The new `last_attempted_provider` migration was applied successfully.

## Database

The transactions table includes:

- M-PESA receipt, phone number, amount, package code
- Lowercase fulfillment statuses
- Provider used
- Last attempted provider binding key
- Atomic worker claim identifier
- Total attempt count
- Background retry attempt count
- Original package code and substitution flag
- Admin alert timestamp and channel
- Client SMS sent timestamp
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
payment_failed
```

Live database verification returns lowercase status values; the existing test
transaction is `fulfilled`.

## Verification

### Webhook tests

- Test A: HTTP 401, `Unauthorized`
- Test B: HTTP 200, `Accepted`
- Test B database state:
  - `status = fulfilled` in the live database
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
- Regression K: synchronous primary and fallback failure enters the sweep with
  `attempt_count = 2`, `background_attempt_count = 0`, then escalates after
  two background attempts.

Full coordinated regression A–K passed in one run:

- A: HTTP 401 Unauthorized.
- B: HTTP 200 Accepted; `fulfilled`, `FAKE_FALLBACK`, total attempts `2`,
  background attempts `0`.
- C: HTTP 200 Already Processed; duplicate warning path verified.
- D–J: all provider, claim, retry, and escalation cases passed.
- K: synchronous double-failure was sweep-eligible and escalated after two
  background attempts.

Real scheduler verification:

- Sequential test seed: `00:01:08` UTC, with background attempt `0`.
- First retry ran at `00:03:00` UTC, after the one-minute eligibility point
  (`00:02:08`) and the next scheduler tick.
- The fake fallback succeeded, so the transaction became fulfilled and no
  second retry was scheduled in that sequential run.
- Separate second-retry timing fixture: seed `23:53:53` UTC with background
  attempt `1`; it ran at `23:57:00` UTC, the first scheduler tick after the
  three-minute delay.
- A full two-failure sequential wall-clock run cannot be exercised with the
  current fake bindings because the fallback is configured to succeed.
- An initial timing fixture seeded directly through MySQL was invalid because
  its local timestamp conflicted with Laravel's UTC application time; it was
  discarded and the timing test was rerun with an Eloquent-generated UTC
  timestamp.

Scheduler concurrency verification:

- 300 queued transactions were loaded.
- Two `schedule:run` processes started in the same minute.
- One sweep ran; the other reported `No scheduled commands are ready to run`,
  confirming the scheduler overlap mutex.

Additional checks:

- Laravel tests: 2 passed, 2 assertions.
- PHP syntax checks passed.
- All migrations applied.
- Scheduler registered successfully.
- Full Laravel regression suite: 2 tests passed, 2 assertions.
- Full PHP syntax checks passed.
- Live database cleanup confirmed zero queued jobs and lowercase statuses.
- L: Telegram primary alert passed with no SMS request.
- M: Telegram failure/unconfigured state is recorded as an alert failure.
- N: Telegram failure left the transaction `needs_attention` with
  `alert_channel = none`.
- O: A second alert dispatch made no additional send.
- P: Synchronous fulfillment sent one customer SMS.
- Q: Background fulfillment sent one customer SMS.
- R: `checkStatus()` fulfillment sent one customer SMS.
- S: SMS failure left fulfillment intact.
- T: Duplicate customer SMS dispatch was idempotent.
- U: Disabled customer SMS sent nothing.
- V: Unrestricted mapping delivered the original package and standard SMS.
- W: Restricted mapping delivered the fallback package and substitution SMS.
- X: Restricted mapping without fallback escalated without provider or customer
  SMS delivery.
- Y: Unmapped amount escalated without provider or customer SMS delivery.
- Explicit X/Y controller verification confirmed `alert_channel = telegram` and
  non-null `alert_sent_at` for both rows; no customer SMS requests were made.
- Z: Background retry used the stored substitution and preserved its SMS
  wording without re-resolving.

The fix-only verification for the queue and provider-status corrections is
documented above and in `run-reports/2026-09-07T060135-0500.json`.

## Intentionally Not Implemented

- WhatsApp and SMS admin alerts
- Customer SMS for `needs_attention` transactions remains intentionally
  unimplemented pending the support-workflow decision.
- Bundle mapping rows are populated by `BundleMappingSeeder`; the verified
  local database currently contains 11 Safaricom mappings.
- M-PESA `ValidationURL`
- Reconciliation against M-PESA statements
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
5b9c50d Add background retry counter
dfcb969 Separate background retry budget
b42c1f8 Add admin alert audit fields
63cdf08 Dispatch Telegram and SMS admin alerts
```

Run reports are stored under `run-reports/` and excluded from Git commits.

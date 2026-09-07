# Okoa

Okoa is a Safaricom data, SMS, and minutes reseller in Kenya. Customers buy
bundles online and pay via M-PESA; the app ingests the payment, resolves the
amount to a package code, and delivers the bundle through an airtime/data
provider.

## Stack

- Laravel 11, PHP 8.4, MySQL
- Tailwind CSS + Alpine.js for the customer-facing UI
- Vite for asset compilation

## How it works

Two payment paths converge on the same fulfillment logic:

```
C2B (Till/Paybill):  MpesaWebhookController → Transaction::create() → FulfillmentService
STK (Web checkout):  OrderController → MpesaService::initiateStkPush() →
                     MpesaStkCallbackController (matched by CheckoutRequestID) →
                     FulfillmentService
```

`FulfillmentService::attemptCascade()` resolves the bundle mapping if needed,
tries the primary provider synchronously, falls back synchronously on a clean
fast-fail, marks the transaction `fulfilled` on success, or `queued_for_retry`
on failure/timeout.

### Background retry sweep

`routes/console.php` registers a scheduled closure (`everyMinute()`,
`withoutOverlapping(5)`) that queries `queued_for_retry` transactions past
their backoff window and calls `(new FulfillOrderJob())->handle($tx->id)`
**directly and synchronously** — there is no queue worker.

`FulfillOrderJob` deliberately does **not** implement `ShouldQueue`. The
target is cPanel shared hosting with no persistent process to run
`php artisan queue:work`. Reintroducing `ShouldQueue` or `::dispatch()` on this
job without verifying a queue consumer exists in the deployment environment
will silently break background retries.

### Provider status checks

When a provider call times out (as opposed to a clean fast-fail), the outcome
is genuinely unknown. `last_attempted_provider` records which adapter needs its
status checked before any retry; `FulfillOrderJob` resolves that adapter via
`app($transaction->last_attempted_provider)` and calls `checkStatus()` first.

`AfricasTalkingAdapter::checkStatus()` is currently a stub returning `UNKNOWN`
— their real status-lookup endpoint needs a stored provider request ID this
codebase doesn't capture yet. Only `FakeProviderAdapter` has a real
configurable implementation, used for testing.

## Status

The core payment-to-fulfillment pipeline is built and independently verified
for both payment paths. STK Push's payment-ingestion side has been verified
against Safaricom's real Daraja sandbox (both success and failure outcomes).
Fulfillment has been verified against fake provider adapters only — no real
Africa's Talking credentials are wired in yet.

See `STATUS.md` (local, not committed) for the full audit trail and next steps.

## Local development

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

The dev database is MySQL (`DB_DATABASE` in `.env`). Tests run against the
same database — they create and clean up their own rows.

## Testing

```bash
php artisan test
```

## Deployment

This project targets cPanel shared hosting (account `mcdaveco`). See the
deployment checklist in `STATUS.md` — it is not yet deployed.

## License

MIT
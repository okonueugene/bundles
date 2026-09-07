@extends('layouts.app')

@section('title', 'Waiting for payment — Okoa')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-8">
    <div
        x-data="waitingApp(@js($transaction->order_reference))"
        x-cloak
    >
        <div class="rounded-xl border border-okoa-border bg-white p-6 shadow-sm text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-ok-light">
                <svg class="h-6 w-6 text-ok" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="mt-4 text-lg font-bold">Payment request sent</h1>
            <p class="mt-2 text-sm text-okoa-muted">Check your phone and enter your M-PESA PIN.</p>

            <div class="mt-6 rounded-lg bg-slate-50 p-4 text-left space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-okoa-muted">Amount</span>
                    <span class="font-semibold">KSh {{ number_format((float) $transaction->amount, 0) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-okoa-muted">Phone</span>
                    <span class="font-semibold">{{ $displayPhone }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-okoa-muted">Bundle</span>
                    <span class="font-semibold">{{ $product?->description ?? 'Safaricom bundle' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-okoa-muted">Reference</span>
                    <span class="font-semibold">{{ $transaction->order_reference }}</span>
                </div>
            </div>

            <div class="mt-6" role="status" aria-live="polite">
                <div class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-okoa-muted" x-show="!timedOut">
                    <svg class="mr-1.5 h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Waiting for confirmation
                </div>
                <p x-show="timedOut" class="text-sm text-okoa-muted">
                    We're still waiting for confirmation. You can check your order status below.
                </p>
            </div>

            <div class="mt-6">
                <a href="{{ route('orders.show', $transaction->order_reference) }}" class="inline-flex min-h-11 items-center rounded-lg bg-ok px-4 py-2 text-sm font-semibold text-white hover:bg-ok-dark">
                    View order status
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
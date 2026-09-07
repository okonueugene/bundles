@extends('layouts.app')

@section('title', 'Checkout — Okoa')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-8">
    <a href="{{ route('catalog.index') }}" class="inline-flex items-center text-sm text-okoa-muted hover:text-ok">
        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to bundles
    </a>

    <div class="mt-6 rounded-xl border border-okoa-border bg-white p-6 shadow-sm">
        <h1 class="text-lg font-bold">Checkout</h1>

        <div class="mt-4 rounded-lg bg-ok-light p-4">
            <p class="text-xs font-semibold text-okoa-muted uppercase tracking-wide">Safaricom {{ ucfirst($product->type) }}</p>
            <p class="mt-1 text-base font-semibold">{{ $product->description }}</p>
            <p class="mt-1 text-sm text-okoa-muted">Valid for {{ $product->validity ?: '24 hours' }}</p>
            <p class="mt-2 text-lg font-bold text-ok">KSh {{ number_format((float) $product->amount, 0) }}</p>
        </div>

        <form
            x-data="checkoutApp(@js(old('phone', '')))"
            x-on:submit="submit($event)"
            x-cloak
            method="POST"
            action="{{ route('orders.store') }}"
            class="mt-6 space-y-4"
        >
            @csrf
            <input type="hidden" name="product" value="{{ $product->slug }}">

            <div>
                <label for="phone" class="block text-sm font-medium">Phone number</label>
                <p class="mt-1 text-xs text-okoa-muted">Enter the Safaricom number to receive the bundle.</p>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    x-model="phone"
                    x-on:input="validatePhone()"
                    placeholder="0712 345 678"
                    autocomplete="tel"
                    class="mt-2 block w-full rounded-lg border border-okoa-border bg-white px-4 py-3 text-sm shadow-sm focus:border-ok focus:outline-none focus:ring-1 focus:ring-ok"
                    required
                >
                <p x-show="phoneError" x-text="phoneError" class="mt-1 text-xs text-red-600"></p>
                @error('phone')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if (session('error'))
                <p class="text-sm text-red-600">{{ session('error') }}</p>
            @endif

            <button
                type="submit"
                x-bind:disabled="!isValid || submitting"
                x-text="submitting ? 'Sending STK Prompt...' : 'Pay KSh {{ number_format((float) $product->amount, 0) }} via M-PESA'"
                class="w-full min-h-12 rounded-lg bg-ok px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-ok-dark disabled:opacity-50 disabled:cursor-not-allowed transition"
            ></button>

            <p class="text-center text-xs text-okoa-muted">
                You will receive an M-PESA prompt on this phone to complete payment.
            </p>
        </form>
    </div>
</div>
@endsection
